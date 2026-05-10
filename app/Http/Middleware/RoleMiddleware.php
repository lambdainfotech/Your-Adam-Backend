<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login to access this resource.',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            return redirect()->route('admin.login');
        }

        $user = auth()->user();
        $userRole = $user->role?->slug;

        if (empty($roles) || !in_array($userRole, $roles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have the required role to access this resource.',
                    'error_code' => 'FORBIDDEN',
                ], 403);
            }

            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
