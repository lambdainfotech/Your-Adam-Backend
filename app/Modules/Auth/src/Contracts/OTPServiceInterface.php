<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\DTOs\OTPSendDTO;
use App\Modules\Auth\DTOs\OTPVerifyDTO;

interface OTPServiceInterface
{
    /**
     * Send OTP to the given mobile number.
     *
     * @param OTPSendDTO $dto
     * @return string Reference ID for the OTP
     */
    public function sendOTP(OTPSendDTO $dto): string;

    /**
     * Verify the OTP code.
     *
     * @param OTPVerifyDTO $dto
     * @return bool True if OTP is valid
     */
    public function verifyOTP(OTPVerifyDTO $dto): bool;

    /**
     * Resend OTP using the reference ID.
     *
     * @param string $reference
     * @return string New reference ID
     */
    public function resendOTP(string $reference): string;
}
