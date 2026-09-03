<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(
        string $email,
        string $password,
        bool $remember = false
    ): User {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (
            ! $user ||
            ! Hash::check($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive.',
            ]);
        }

        Auth::guard('web')->login($user, $remember);

        request()->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
        ]);

        return $user->fresh([
            'branch',
            'store',
            'stores',
            'roles',
            'permissions',
        ]);
    }

    public function logout(User $user): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();
    }

    public function logoutAll(User $user): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        $user->tokens()->delete();
    }

    public function forgotPassword(
        string $email
    ): string {
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [
                    __($status),
                ],
            ]);
        }

        return __($status);
    }

    public function resetPassword(
        string $email,
        string $token,
        string $password
    ): string {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (User $user) use ($password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [
                    __($status),
                ],
            ]);
        }

        return __($status);
    }
}
