<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\OTPServiceInterface;
use App\Modules\Auth\DTOs\OTPSendDTO;
use App\Modules\Auth\DTOs\OTPVerifyDTO;
use App\Modules\Auth\Repositories\OTPRepository;
use App\Modules\Core\Abstracts\BaseService;

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

        // Create new OTP
        $otp = $this->otpRepository->createOTP($dto->mobile, $dto->purpose);

        // Send SMS (dispatch job)
        SendOTPJob::dispatch($otp->mobile, $otp->code);

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

        if ($otp->code !== $dto->otp) {
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

        // Create new OTP with same details
        $newOtp = $this->otpRepository->createOTP($existingOtp->mobile, $existingOtp->purpose);

        // Send SMS
        SendOTPJob::dispatch($newOtp->mobile, $newOtp->code);

        return $newOtp->reference;
    }
}
