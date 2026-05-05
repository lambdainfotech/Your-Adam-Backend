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
 * - Web: Tokens are passed via HTTP-Only cookies
 * - API: Tokens are passed via Authorization header ONLY
 */
class JWTAuthMiddleware
{
    protected string $cookieName = 'jwt_token';

    protected array $excludedRoutes = [
        'admin.login',
        'admin.login.post',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        $authenticated = $this->authenticate($request);

        if (!$authenticated) {
            return $this->unauthenticated($request);
        }

        return $next($request);
    }

    protected function isExcludedRoute(Request $request): bool
    {
        if ($request->routeIs($this->excludedRoutes)) {
            return true;
        }

        $path = $request->path();
        if (str_starts_with($path, 'admin/login') || $path === 'admin/login') {
            return true;
        }

        return false;
    }

    protected function authenticate(Request $request): bool
    {
        try {
            $token = null;
            $source = 'none';

            // For API routes: ONLY accept Authorization header (Bearer token)
            // Never accept cookies on API routes
            if ($request->is('api/*')) {
                $authHeader = $request->header('Authorization');
                if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                    $token = $matches[1];
                    $source = 'header';
                }
            } else {
                // For web routes: check cookie first, then header
                $token = $request->cookie($this->cookieName);
                if ($token) {
                    $source = 'cookie';
                } elseif (($authHeader = $request->header('Authorization')) && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                    $token = $matches[1];
                    $source = 'header';
                }
            }

            if (!$token) {
                \Log::debug('JWTAuthMiddleware: No token found', ['path' => $request->path(), 'is_api' => $request->is('api/*')]);
                return false;
            }

            \Log::debug('JWTAuthMiddleware: Token found', ['source' => $source, 'path' => $request->path()]);

            JWTAuth::setToken($token);
            $user = JWTAuth::authenticate();

            if ($user) {
                auth()->setUser($user);
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
                \Log::debug('JWTAuthMiddleware: Authenticated', ['user_id' => $user->id]);
                return true;
            }

            \Log::debug('JWTAuthMiddleware: Token invalid - no user');
            return false;

        } catch (TokenExpiredException $e) {
            \Log::debug('JWTAuthMiddleware: Token expired');
            return false;
        } catch (TokenInvalidException $e) {
            \Log::debug('JWTAuthMiddleware: Token invalid');
            return false;
        } catch (JWTException $e) {
            \Log::debug('JWTAuthMiddleware: JWT exception', ['message' => $e->getMessage()]);
            return false;
        } catch (\Exception $e) {
            \Log::debug('JWTAuthMiddleware: Exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    protected function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login to access this resource.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Clear invalid token cookie to prevent redirect loops
        return redirect()
            ->guest(route('admin.login'))
            ->withoutCookie($this->cookieName);
    }
}
