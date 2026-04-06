<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Unified JWT Authentication Middleware
 * 
 * Handles authentication for both web and API routes using JWT tokens.
 * - Web: Tokens are passed via HTTP-Only cookies (encrypted by us, not Laravel)
 * - API: Tokens are passed via Authorization header
 */
class JWTAuthMiddleware
{
    /**
     * Cookie name for JWT token storage
     */
    protected string $cookieName = 'jwt_token';

    /**
     * Routes that should be excluded from JWT auth check
     */
    protected array $excludedRoutes = [
        'admin.login',
        'admin.login.post',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip JWT auth for login routes to avoid redirect loops
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Try to authenticate the user
        $authenticated = $this->authenticate($request);

        if (!$authenticated) {
            return $this->unauthenticated($request);
        }

        return $next($request);
    }

    /**
     * Check if route should be excluded from JWT auth
     */
    protected function isExcludedRoute(Request $request): bool
    {
        // Check by route name
        if ($request->routeIs($this->excludedRoutes)) {
            return true;
        }

        // Check by path
        $path = $request->path();
        if (str_starts_with($path, 'admin/login') || $path === 'admin/login') {
            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate the user via JWT token
     */
    protected function authenticate(Request $request): bool
    {
        try {
            // First, try to get token from cookie (web requests)
            $token = $this->getTokenFromCookie($request);

            // If no cookie token, try Authorization header (API requests)
            if (!$token) {
                $token = JWTAuth::getToken();
            }

            // If still no token, user is not authenticated
            if (!$token) {
                return false;
            }

            // Set the token and authenticate
            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if ($user) {
                // Set the authenticated user in the request
                auth()->setUser($user);
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
                return true;
            }

            return false;

        } catch (TokenExpiredException $e) {
            // Token expired - let the caller handle this
            // We don't auto-refresh here to avoid side effects in middleware
            return false;
        } catch (TokenInvalidException $e) {
            return false;
        } catch (JWTException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get JWT token from cookie
     * Laravel's EncryptCookies middleware automatically decrypts the cookie.
     */
    protected function getTokenFromCookie(Request $request): ?string
    {
        // Laravel's EncryptCookies middleware automatically decrypts cookies
        // So $request->cookie() returns the decrypted value directly
        return $request->cookie($this->cookieName);
    }

    /**
     * Handle unauthenticated request
     */
    protected function unauthenticated(Request $request): Response
    {
        // For API requests, return JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login to access this resource.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // For web requests, store intended URL and redirect to login page
        return redirect()->guest(route('admin.login'));
    }
}
