<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\V1\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->boolean('remember')
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            // 'data' => [
            //     'token' => $result['token'],
            //     'user' => $result['user'],
            //     'roles' => $result['user']->getRoleNames(),
            //     'permissions' => $result['user']
            //         ->getAllPermissions()
            //         ->pluck('name'),
            // ],
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout(
            $request->user()
        );

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $this->authService->logoutAll(
            $request->user()
        );

        return response()->json([
            'message' =>
                'Logged out from all devices successfully.',
        ]);
    }

    public function me(Request $request)
{
    $user = $request->user()->load(['branch', 'store', 'stores', 'roles']);

    return response()->json([
        'data' => [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ],
    ]);
}

    public function forgotPassword(
        ForgotPasswordRequest $request
    ) {
        $message = $this->authService->forgotPassword(
            $request->string('email')->toString()
        );

        return response()->json([
            'message' => $message,
        ]);
    }

    public function resetPassword(
        ResetPasswordRequest $request
    ) {
        $message = $this->authService->resetPassword(
            $request->string('email')->toString(),
            $request->string('token')->toString(),
            $request->string('password')->toString()
        );

        return response()->json([
            'message' => $message,
        ]);
    }
}
