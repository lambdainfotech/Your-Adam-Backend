<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OTPService;
use App\Traits\ApiResponse;
use App\Traits\JWTAuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class OTPAuthController extends Controller
{
    use ApiResponse;
    use JWTAuthTrait;

    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to mobile number
     */
    public function sendOTP(Request $request): JsonResponse
    {
        try {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|min:10|max:15',
            'purpose' => 'nullable|string|in:registration,login,password_reset',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $purpose = $request->input('purpose', 'registration');
        $mobile = $request->input('mobile');

        $result = $this->otpService->sendOTP($mobile, $purpose);

        if (!$result['success']) {
            return $this->error($result['message'], 500);
        }

        return $this->success([
            'reference' => $result['reference'],
            'expires_in' => $result['expires_in'],
            'masked_mobile' => $result['masked_mobile'],
        ], 'OTP sent successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to send OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify OTP and login/register user
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        try {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|min:10|max:15',
            'otp' => 'required|string|size:6',
            'reference' => 'required|string',
            'is_registration' => 'required|boolean',
            'password' => 'required_if:is_registration,true|string|min:6|confirmed',
            'full_name' => 'required_if:is_registration,true|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $mobile = $request->input('mobile');
        $otp = $request->input('otp');
        $reference = $request->input('reference');
        $isRegistration = $request->boolean('is_registration');

        if (!$this->otpService->verifyOTP($mobile, $otp, $reference)) {
            return $this->error('Invalid or expired OTP', 400, ['error_code' => 'INVALID_OTP']);
        }

        if ($isRegistration) {
            $normalizedMobile = preg_replace('/[^0-9]/', '', $mobile);
            if (str_starts_with($normalizedMobile, '0')) {
                $normalizedMobile = '88' . $normalizedMobile;
            } elseif (!str_starts_with($normalizedMobile, '880')) {
                $normalizedMobile = '880' . $normalizedMobile;
            }

            $existingUser = User::where('mobile', $normalizedMobile)->first();
            if ($existingUser) {
                return $this->error('User already exists with this mobile number', 422, ['error_code' => 'USER_EXISTS']);
            }

            $user = $this->otpService->registerUser([
                'mobile' => $mobile,
                'password' => $request->input('password'),
                'full_name' => $request->input('full_name'),
                'email' => $request->input('email'),
            ]);

            $message = 'Registration successful';
        } else {
            $user = $this->otpService->loginWithOTP($mobile);

            if (!$user) {
                return $this->error('No account found with this mobile number', 404, ['error_code' => 'USER_NOT_FOUND']);
            }

            $message = 'Login successful';
        }

        // For OTP auth, generate token directly from user model
        $tokenData = $this->generateTokenForUser($user);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->role?->name,
                'status' => $user->status,
                'email_verified' => !is_null($user->email_verified_at),
                'mobile_verified' => !is_null($user->mobile_verified_at),
                'created_at' => $user->created_at,
            ],
            'tokens' => [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_type' => 'bearer',
                'expires_in' => $tokenData['expires_in'] ?? 3600,
            ],
        ], $message);
        } catch (\Exception $e) {
            return $this->error('Failed to verify OTP: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate token directly for a user (for OTP-based auth)
     */
    protected function generateTokenForUser(User $user): array
    {
        $token = JWTAuth::fromUser($user);

        return [
            'access_token' => $token,
            'refresh_token' => $this->generateRefreshToken(),
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];
    }
}
