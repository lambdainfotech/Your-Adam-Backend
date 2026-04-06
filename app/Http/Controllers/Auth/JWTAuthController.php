<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\JWTAuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Unified JWT Authentication Controller
 * 
 * Handles authentication for both Web and API interfaces using JWT tokens.
 * - Web: Sets HTTP-Only cookie with token
 * - API: Returns JSON with access/refresh tokens
 */
class JWTAuthController extends Controller
{
    use JWTAuthTrait;

    /**
     * Show the login form (Web only)
     */
    public function showLoginForm(): \Illuminate\View\View|RedirectResponse
    {
        // If user has a valid JWT token in cookie, redirect to dashboard
        // Use JWTAuth directly to avoid triggering auth middleware
        try {
            $token = request()->cookie('jwt_token');
            if ($token) {
                \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::setToken($token);
                if (\PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::check()) {
                    return redirect()->route('admin.dashboard');
                }
            }
        } catch (\Exception $e) {
            // Invalid token, continue to show login form
        }

        return view('admin.auth.login');
    }

    /**
     * Handle login request (Web & API)
     * 
     * Web: Redirects to dashboard with cookie
     * API: Returns JSON with tokens
     */
    public function login(Request $request): JsonResponse|RedirectResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            throw new ValidationException($validator);
        }

        $credentials = $request->only('email', 'password');

        // Attempt login
        $tokenData = $this->attemptLogin($credentials);

        if (!$tokenData) {
            // Login failed
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error_code' => 'INVALID_CREDENTIALS',
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Check if user is active
        $user = $this->getAuthenticatedUser();
        if ($user && !$user->status) {
            // Logout the user (invalidate token)
            $this->createLogoutResponse();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated.',
                    'error_code' => 'ACCOUNT_INACTIVE',
                ], 403);
            }

            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Login successful - return appropriate response
        return $this->createLoginResponse(
            tokenData: $tokenData,
            redirectRoute: 'admin.dashboard',
            message: 'Login successful'
        );
    }

    /**
     * Handle logout request (Web & API)
     * 
     * Web: Clears cookie and redirects to login
     * API: Invalidates token and returns JSON
     */
    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        return $this->createLogoutResponse(
            redirectRoute: 'admin.login',
            message: 'You have been logged out successfully.'
        );
    }

    /**
     * Refresh access token (API only)
     * 
     * Returns new access token using refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->input('refresh_token');
            
            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token is required',
                    'error_code' => 'REFRESH_TOKEN_MISSING',
                ], 400);
            }

            $tokenData = $this->refreshAccessToken($refreshToken);

            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired refresh token',
                    'error_code' => 'TOKEN_REFRESH_FAILED',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'tokens' => [
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'],
                        'token_type' => $tokenData['token_type'],
                        'expires_in' => $tokenData['expires_in'],
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token: ' . $e->getMessage(),
                'error_code' => 'TOKEN_REFRESH_FAILED',
            ], 401);
        }
    }

    /**
     * Get authenticated user details (API only)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role?->name,
                    'is_admin' => $user->isAdmin(),
                    'status' => $user->status,
                    'email_verified' => !is_null($user->email_verified_at),
                    'mobile_verified' => !is_null($user->mobile_verified_at),
                    'created_at' => $user->created_at,
                ],
            ],
        ]);
    }

    /**
     * Check if user is authenticated (API only)
     */
    public function check(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'authenticated' => $this->isAuthenticated(),
            ],
        ]);
    }
}
