<?php

namespace App\Services;

use App\Models\OTP;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OTPService
{
    protected MuthobartaSMSService $smsService;

    public function __construct(MuthobartaSMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendOTP(string $mobile, string $purpose = 'registration'): array
    {
        $mobile = $this->normalizeMobile($mobile);

        // Invalidate any previous unverified OTPs for this mobile
        OTP::where('mobile', $mobile)
            ->whereNull('verified_at')
            ->update(['verified_at' => now()]);

        $code = $this->generateCode();
        $reference = Str::random(16);

        OTP::create([
            'mobile' => $mobile,
            'code' => Hash::make($code),
            'reference' => $reference,
            'purpose' => $purpose,
            'attempts' => 0,
            'max_attempts' => 3,
            'expires_at' => now()->addMinutes(5),
        ]);

        $template = \App\Models\Setting::get('sms_otp_template', 'Your verification code is: {code}. It will expire in 5 minutes.');
        $message = str_replace('{code}', $code, $template);
        $smsResult = $this->smsService->sendSMS($mobile, $message);

        if (!$smsResult['success']) {
            return [
                'success' => false,
                'message' => $smsResult['message'] ?? 'Failed to send SMS',
            ];
        }

        return [
            'success' => true,
            'reference' => $reference,
            'expires_in' => 300,
            'masked_mobile' => $this->maskMobile($mobile),
        ];
    }

    public function verifyOTP(string $mobile, string $code, string $reference): bool
    {
        $mobile = $this->normalizeMobile($mobile);

        $otp = OTP::where('mobile', $mobile)
            ->where('reference', $reference)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        if ($otp->hasExceededAttempts()) {
            return false;
        }

        if (!Hash::check($code, $otp->code)) {
            $otp->incrementAttempts();
            return false;
        }

        $otp->markVerified();
        return true;
    }

    public function registerUser(array $data): User
    {
        $mobile = $this->normalizeMobile($data['mobile']);

        return User::create([
            'name' => $data['full_name'] ?? 'User',
            'email' => $data['email'] ?? null,
            'mobile' => $mobile,
            'password' => Hash::make($data['password']),
            'mobile_verified_at' => now(),
            'status' => true,
            'role_id' => $data['role_id'] ?? $this->getDefaultRoleId(),
        ]);
    }

    public function loginWithOTP(string $mobile): ?User
    {
        $mobile = $this->normalizeMobile($mobile);

        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            $user->update(['mobile_verified_at' => now()]);
        }

        return $user;
    }

    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);

        if (str_starts_with($mobile, '0')) {
            $mobile = '88' . $mobile;
        } elseif (str_starts_with($mobile, '880')) {
            // Already in 880 format
        } else {
            $mobile = '880' . $mobile;
        }

        return $mobile;
    }

    protected function maskMobile(string $mobile): string
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

    protected function getDefaultRoleId(): ?int
    {
        $role = \App\Models\Role::where('slug', 'customer')->first();
        return $role?->id;
    }
}
