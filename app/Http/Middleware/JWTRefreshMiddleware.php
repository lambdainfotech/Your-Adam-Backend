<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT Token Refresh Middleware
 * 
 * Automatically refreshes JWT tokens before they expire for web routes.
 * This provides a seamless experience for web users without requiring
 * them to re-login frequently.
 */
class JWTRefreshMiddleware
{
    /**
     * Cookie name for JWT token storage
     */
    protected string $cookieName = 'jwt_token';

    /**
     * Threshold percentage for token refresh (refresh when less than this % remains)
     */
    protected float $refreshThreshold = 0.2; // 20% of TTL remaining

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process for web routes with authenticated user
        if (!auth()->check() || $request->expectsJson() || $request->is('api/*')) {
            return $response;
        }

        // Check if token needs refresh
        if ($this->shouldRefreshToken()) {
            $response = $this->refreshToken($response);
        }

        return $response;
    }

    /**
     * Determine if the token should be refreshed
     */
    protected function shouldRefreshToken(): bool
    {
        try {
            $payload = JWTAuth::payload();
            $expiration = $payload->get('exp');
            $issuedAt = $payload->get('iat');
            
            if (!$expiration || !$issuedAt) {
                return false;
            }

            $totalLifetime = $expiration - $issuedAt;
            $remaining = $expiration - time();
            
            // Refresh if less than threshold of lifetime remains
            return $remaining < ($totalLifetime * $this->refreshThreshold);

        } catch (JWTException $e) {
            return false;
        }
    }

    /**
     * Refresh the JWT token and set new cookie
     */
    protected function refreshToken(Response $response): Response
    {
        try {
            // Refresh the token
            $newToken = JWTAuth::refresh();
            
            // Set new token cookie
            $cookie = $this->createTokenCookie($newToken);
            
            // Add cookie to response
            $response->headers->setCookie($cookie);
            
            // Update the token in JWTAuth for subsequent use
            JWTAuth::setToken($newToken);

        } catch (TokenExpiredException $e) {
            // Token fully expired - user needs to re-login
            // Clear the cookie
            $response->headers->clearCookie($this->cookieName);
        } catch (JWTException $e) {
            // Other errors - don't interrupt the request
            // Token will be refreshed on next request
        }

        return $response;
    }

    /**
     * Create HTTP-Only cookie with token
     */
    protected function createTokenCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        $ttl = config('jwt.ttl', 60); // minutes
        
        return Cookie::make(
            name: $this->cookieName,
            value: $token,
            minutes: $ttl,
            path: '/',
            domain: config('session.domain'),
            secure: config('session.secure', false),
            httpOnly: true,
            sameSite: config('session.same_site', 'lax')
        );
    }
}
