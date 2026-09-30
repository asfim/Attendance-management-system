<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $user = $this->userService->authenticate(
            $request->input('email'),
            $request->input('password')
        );

        if ($user) {
            Auth::login($user);
            return $this->redirectUserByRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or your account is inactive.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function dashboardRedirect()
    {
        return Auth::check()
            ? $this->redirectUserByRole(Auth::user())
            : redirect()->route('home');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        // Mocking token creation
        $token = Str::random(60);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Normally we'd mail it, but for our ERP sandboxed environment, we can set a session message
        // showing the mock link or a success message.
        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function showResetPasswordForm(string $token, Request $request)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = \DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if ($record && Hash::check($request->token, $record->token)) {
            $user = User::where('email', $request->email)->firstOrFail();
            $this->userService->changePassword($user, $request->password);

            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()->route('login')->with('status', 'Your password has been reset successfully!');
        }

        return back()->withErrors(['email' => 'Invalid email or token expired.']);
    }

    public function showProfile()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('status', 'Profile updated successfully.');
    }

    protected function redirectUserByRole(User $user)
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return redirect()->route('admin.attendance-suite.dashboard');
        } elseif ($user->hasRole(['teacher', 'hr', 'staff', 'accountant', 'receptionist', 'employee'])) {
            return redirect()->route('employee.dashboard');
        }

        return redirect()->route('admin.attendance-suite.dashboard');
    }
}
