<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Modules\Core\Abstracts\BaseController;
use App\Modules\Auth\Contracts\AuthServiceInterface;
use App\Modules\Auth\Contracts\OTPServiceInterface;
use App\Modules\Auth\DTOs\OTPSendDTO;
use App\Modules\Auth\DTOs\OTPVerifyDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Http\Requests\OTPSendRequest;
use App\Modules\Auth\Http\Requests\OTPVerifyRequest;
use App\Modules\Auth\Http\Resources\TokenResource;
use App\Modules\Auth\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 */
class AuthController extends BaseController
{
    public function __construct(
        private AuthServiceInterface $authService,
        private OTPServiceInterface $otpService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/mobile/send-otp",
     *     summary="Send OTP to mobile",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "purpose"},
     *             @OA\Property(property="mobile", type="string", example="+8801712345678"),
     *             @OA\Property(property="purpose", type="string", enum={"registration", "login", "password_reset"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="reference", type="string"),
     *             @OA\Property(property="expires_in", type="integer", example=300),
     *             @OA\Property(property="masked_mobile", type="string")
     *         )
     *     )
     * )
     */
    public function sendOTP(OTPSendRequest $request): JsonResponse
    {
        $dto = OTPSendDTO::fromRequest($request->validated());
        $reference = $this->otpService->sendOTP($dto);

        return $this->successResponse([
            'reference' => $reference,
            'expires_in' => 300,
            'masked_mobile' => $this->maskMobile($dto->mobile),
        ], 'OTP sent successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/mobile/verify",
     *     summary="Verify OTP and login/register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "otp", "reference", "is_registration"},
     *             @OA\Property(property="mobile", type="string"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="reference", type="string"),
     *             @OA\Property(property="is_registration", type="boolean"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string"),
     *             @OA\Property(property="full_name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *             @OA\Property(property="tokens", ref="#/components/schemas/TokenResource")
     *         )
     *     )
     * )
     */
    public function verifyOTP(OTPVerifyRequest $request): JsonResponse
    {
        $dto = OTPVerifyDTO::fromRequest($request->validated());

        if (!$this->otpService->verifyOTP($dto)) {
            return $this->errorResponse('Invalid or expired OTP', 400, null, 'INVALID_OTP');
        }

        if ($dto->isRegistration) {
            $result = $this->authService->register(RegisterDTO::fromRequest([
                'mobile' => $dto->mobile,
                'password' => $dto->password,
                'full_name' => $dto->fullName,
                'email' => $dto->email,
            ]));
        } else {
            $result = $this->authService->loginWithOTP($dto->mobile);
        }

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'tokens' => new TokenResource($result['tokens']),
        ], $dto->isRegistration ? 'Registration successful' : 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Login with mobile and password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "password"},
     *             @OA\Property(property="mobile", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="device_name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     *             @OA\Property(property="tokens", ref="#/components/schemas/TokenResource")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'tokens' => new TokenResource($result['tokens']),
        ], 'Login successful');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     summary="Refresh access token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="tokens", ref="#/components/schemas/TokenResource")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        $tokens = $this->authService->refresh($request->input('refresh_token'));

        return $this->successResponse([
            'tokens' => new TokenResource($tokens),
        ], 'Token refreshed successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Logout user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse([], 'Logout successful');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Get authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse([
            'user' => new UserResource($request->user()),
        ], 'User details retrieved successfully');
    }

    private function maskMobile(string $mobile): string
    {
        $length = strlen($mobile);
        
        if ($length < 8) {
            return $mobile;
        }

        $visibleStart = 4;
        $visibleEnd = 2;
        $maskedLength = $length - $visibleStart - $visibleEnd;

        return substr($mobile, 0, $visibleStart) . str_repeat('*', $maskedLength) . substr($mobile, -$visibleEnd);
    }
}
