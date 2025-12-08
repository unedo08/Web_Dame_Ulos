<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized - Invalid or missing token'
            ], 401);
        }

        // user tidak punya role
        if (!$user->role) {
            return response()->json([
                'error' => 'Forbidden - User has no role assigned'
            ], 403);
        }

        // role tidak cocok
        if (!in_array($user->role->name, $roles)) {
            return response()->json([
                'error' => 'Forbidden - You do not have access to this resource'
            ], 403);
        }

        return $next($request);
    }
}
