<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Allow Super Admin and Admin explicitly
        if ($user->isSuperAdmin() || $user->hasRole('admin')) {
            return $next($request);
        }

        // Must have at least one permission to access admin
        if (!in_array($user->role->name, ['teacher', 'accountant', 'librarian', 'receptionist', 'hr', 'staff']) || $user->role->permissions()->count() == 0) {
            abort(403, 'Unauthorized action. You do not have the required permissions.');
        }

        // Dynamic Route Permission Checking
        $routeName = $request->route()->getName();
        if ($routeName) {
            // Check Create / Store actions
            if (preg_match('/^admin\.([a-zA-Z0-9_\-\.]+)\.(create|store|allocate|save-day)$/', $routeName, $matches)) {
                $module = $this->getModuleKey($matches[1]);
                if (!$user->hasPermission("create_{$module}")) {
                    abort(403, "Unauthorized. Requires 'create_{$module}' permission.");
                }
            }
            
            // Check Edit / Update actions
            if (preg_match('/^admin\.([a-zA-Z0-9_\-\.]+)\.(edit|update|promote|pay|unlock)$/', $routeName, $matches)) {
                $module = $this->getModuleKey($matches[1]);
                if (!$user->hasPermission("edit_{$module}")) {
                    abort(403, "Unauthorized. Requires 'edit_{$module}' permission.");
                }
            }
            
            // Check Delete / Destroy actions
            if (preg_match('/^admin\.([a-zA-Z0-9_\-\.]+)\.(destroy|delete|release)$/', $routeName, $matches)) {
                $module = $this->getModuleKey($matches[1]);
                if (!$user->hasPermission("delete_{$module}")) {
                    abort(403, "Unauthorized. Requires 'delete_{$module}' permission.");
                }
            }
        }

        return $next($request);
    }

    private function getModuleKey($routePrefix)
    {
        $map = [
            'staff' => 'staff',
            'staff-attendance' => 'staff_attendance',
            'students' => 'student',
            'academics' => 'academic',
            'classrooms' => 'academic',
            'exams' => 'academic',
            'holidays' => 'academic',
            'marks' => 'academic',
            'results' => 'academic',
            'routines' => 'academic',
            'subjects' => 'academic',
            'fees' => 'fee',
            'accounts' => 'account',
            'payroll' => 'payroll',
            'library' => 'book',
            'hostel' => 'hostel',
            'transport' => 'transport',
            'food' => 'food',
            'inventory' => 'inventory',
            'notices' => 'notice',
            'certificates' => 'certificate',
            'roles' => 'role',
            'settings' => 'settings',
            'cms' => 'cms',
            'sms-configuration' => 'sms',
            'sms' => 'sms'
        ];
        
        $parts = explode('.', $routePrefix);
        $mainPrefix = $parts[0];
        
        return $map[$mainPrefix] ?? $mainPrefix;
    }
}
