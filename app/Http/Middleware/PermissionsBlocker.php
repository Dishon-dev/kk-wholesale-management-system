<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionsBlocker
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($permissions)) {
            return response()->json([
                'success' => false,
                'message' => 'No permission has been specified for this endpoint.',
            ], 403);
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }
        
        return $next($request);
    }
}
