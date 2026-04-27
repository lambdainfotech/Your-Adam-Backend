<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;

/**
 * JWT Authentication Trait
 * 
 * Provides common JWT authentication methods for controllers.
 * Handles both web (cookie-based) and API (header-based) responses.
 */
trait JWTAuthTrait
{
    /**
     * Cookie name for JWT token
     */
    protected string $cookieName = 'jwt_token';

    /**
     * Login user and return appropriate response with JWT token
     *
     * @param array $credentials User credentials
     * @param string|null $guard Auth guard (not used, kept for compatibility)
     * @return array|bool Returns token data on success, false on failure
     */
    protected function attemptLogin(array $credentials, ?string $guard = null): array|bool
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return false;
        }

        // Generate a refresh token from the authenticated user
        $refreshToken = $this->generateRefreshToken();

        return [
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }

    /**
     * Generate refresh token from current authenticated user
     * This creates a new token specifically for refresh purposes
     */
    protected function generateRefreshToken(): ?string
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        // Save original state
        $originalClaims = JWTAuth::getCustomClaims();
        $originalTTL = JWTAuth::factory()->getTTL();

        // Set refresh-specific claims and TTL (2 weeks)
        $refreshTTL = config('jwt.refresh_ttl', 20160);
        JWTAuth::factory()->setTTL($refreshTTL);
        JWTAuth::customClaims(['type' => 'refresh']);

        // Generate the refresh token
        $token = JWTAuth::fromUser($user);

        // Restore original state for subsequent access tokens
        JWTAuth::customClaims($originalClaims);
        JWTAuth::factory()->setTTL($originalTTL);

        return $token;
    }

    /**
     * Create login response based on request type (web or API)
     *
     * @param array $tokenData Token data from attemptLogin
     * @param string $redirectRoute Route to redirect web users
     * @param string $message Success message
     * @return JsonResponse|RedirectResponse
     */
    protected function createLoginResponse(
        array $tokenData,
        string $redirectRoute = 'admin.dashboard',
        string $message = 'Login successful'
    ): JsonResponse|RedirectResponse {
        $request = request();

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user' => auth()->user(),
                    'tokens' => [
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'],
                        'token_type' => $tokenData['token_type'],
                        'expires_in' => $tokenData['expires_in'],
                    ],
                ],
            ]);
        }

        // Web request - set cookie and redirect
        $cookie = $this->createTokenCookie($tokenData['access_token']);

        // Check if $redirectRoute is a full URL or a route name
        if (filter_var($redirectRoute, FILTER_VALIDATE_URL) || str_starts_with($redirectRoute, 'http')) {
            // It's a URL, use redirect()->to()
            return redirect()
                ->to($redirectRoute)
                ->with('success', $message)
                ->cookie($cookie);
        }

        // It's a route name, use redirect()->route()
        return redirect()
            ->route($redirectRoute)
            ->with('success', $message)
            ->cookie($cookie);
    }

    /**
     * Create logout response based on request type
     *
     * @param string $redirectRoute Route to redirect web users
     * @param string $message Logout message
     * @return JsonResponse|RedirectResponse
     */
    protected function createLogoutResponse(
        string $redirectRoute = 'admin.login',
        string $message = 'Logout successful'
    ): JsonResponse|RedirectResponse {
        $request = request();

        // Invalidate token
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Exception $e) {
            // Token might already be invalid, continue with logout
        }

        // API request - return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        // Web request - clear cookie and redirect
        return redirect()
            ->route($redirectRoute)
            ->with('success', $message)
            ->withoutCookie($this->cookieName);
    }

    /**
     * Create HTTP-Only cookie with JWT token
     */
    protected function createTokenCookie(string $token): \Symfony\Component\HttpFoundation\Cookie
    {
        $ttl = config('jwt.ttl', 120); // minutes

        return Cookie::make(
            name: $this->cookieName,
            value: $token,
            minutes: $ttl,
            path: '/',
            domain: null,
            secure: config('jwt.cookie_secure', true),
            httpOnly: true,
            sameSite: 'lax'
        );
    }

    /**
     * Get current authenticated user
     */
    protected function getAuthenticatedUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth()->user();
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Refresh access token
     *
     * @param string|null $refreshToken Refresh token
     * @return array|null Token data or null on failure
     */
    protected function refreshAccessToken(?string $refreshToken = null): ?array
    {
        try {
            if (!$refreshToken) {
                return null;
            }

            // Manually decode payload to verify this is a refresh token
            // (works even for expired tokens — base64 decode needs no validation)
            $parts = explode('.', $refreshToken);
            if (count($parts) !== 3) {
                return null;
            }

            $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            $payload = json_decode($payloadJson, true);

            if (!is_array($payload) || ($payload['type'] ?? 'access') !== 'refresh') {
                return null;
            }

            // Get user from payload
            $user = \App\Models\User::find($payload['sub'] ?? null);
            if (!$user) {
                return null;
            }

            // Invalidate the old token
            try {
                JWTAuth::setToken($refreshToken)->invalidate();
            } catch (\Exception $e) {
                // Token might already be invalid, continue
            }

            // Generate new access token
            $newToken = JWTAuth::fromUser($user);

            return [
                'access_token' => $newToken,
                'refresh_token' => $this->generateRefreshToken(),
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
