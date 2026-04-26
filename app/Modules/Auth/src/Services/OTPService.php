<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\OTPServiceInterface;
use App\Modules\Auth\DTOs\OTPSendDTO;
use App\Modules\Auth\DTOs\OTPVerifyDTO;
use App\Modules\Auth\Repositories\OTPRepository;
use App\Modules\Core\Abstracts\BaseService;
use App\Modules\Notification\Contracts\SMSServiceInterface;

class OTPService extends BaseService implements OTPServiceInterface
{
    public function __construct(
        private OTPRepository $otpRepository,
        private SMSServiceInterface $smsService
    ) {
    }

    public function sendOTP(OTPSendDTO $dto): string
    {
        // Revoke existing OTPs for this mobile+purpose
        $this->otpRepository->revokeExistingOTPs($dto->mobile, $dto->purpose);

        // Generate raw code for SMS
        $rawCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create new OTP with hashed code
        $otp = $this->otpRepository->createOTP($dto->mobile, $dto->purpose, $rawCode);

        // Send SMS (dispatch job) with raw code
        SendOTPJob::dispatch($otp->mobile, $rawCode);

        return $otp->reference;
    }

    public function verifyOTP(OTPVerifyDTO $dto): bool
    {
        $otp = $this->otpRepository->findValidOTP($dto->mobile, $dto->reference);

        if (!$otp) {
            return false;
        }

        if ($otp->hasExceededAttempts()) {
            return false;
        }

        if (!\Hash::check($dto->otp, $otp->code)) {
            $otp->incrementAttempts();
            return false;
        }

        $otp->markVerified();

        return true;
    }

    public function resendOTP(string $reference): string
    {
        $existingOtp = $this->otpRepository->findByReference($reference);

        if (!$existingOtp) {
            throw new \RuntimeException('OTP reference not found');
        }

        // Revoke the existing OTP
        $this->otpRepository->revokeExistingOTPs($existingOtp->mobile, $existingOtp->purpose);

        // Generate raw code for SMS
        $rawCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create new OTP with same details
        $newOtp = $this->otpRepository->createOTP($existingOtp->mobile, $existingOtp->purpose, $rawCode);

        // Send SMS with raw code
        SendOTPJob::dispatch($newOtp->mobile, $rawCode);

        return $newOtp->reference;
    }
}
