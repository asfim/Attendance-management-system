<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status === 'active') {
                return $user;
            }
        }

        return null;
    }

    public function changePassword(User $user, string $newPassword): bool
    {
        return $this->userRepository->update($user->id, [
            'password' => Hash::make($newPassword),
        ]);
    }
}
