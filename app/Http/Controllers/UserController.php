<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * List users.
     */
    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $users = $this->userService->paginate(
            $perPage
        );

        return response()->json([
            'data' => $users->items(),

            'meta' => [
                'current_page' =>
                    $users->currentPage(),

                'last_page' =>
                    $users->lastPage(),

                'per_page' =>
                    $users->perPage(),

                'total' =>
                    $users->total(),
            ],
        ]);
    }

    /**
     * Create user.
     */
    public function store(
        StoreUserRequest $request
    ) {
        $user = $this->userService->create(
            $request->validated()
        );

        return response()->json([
            'message' =>
                'User created successfully.',

            'data' => $user,
        ], 201);
    }

    /**
     * Show user.
     */
    public function show(
        User $user
    ) {
        $user = $this->userService->find(
            $user
        );

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Update user.
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        $user = $this->userService->update(
            $user,
            $request->validated()
        );

        return response()->json([
            'message' =>
                'User updated successfully.',

            'data' => $user,
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(
        Request $request,
        User $user
    ) {
        if (
            $request->user()->is($user)
        ) {
            return response()->json([
                'message' =>
                    'You cannot delete your own account.',
            ], 422);
        }

        $this->userService->delete($user);

        return response()->json([
            'message' =>
                'User deleted successfully.',
        ]);
    }
}