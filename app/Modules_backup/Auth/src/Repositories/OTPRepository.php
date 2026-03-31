<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Modules\Auth\Models\OTP;
use App\Modules\Core\Abstracts\BaseRepository;

class OTPRepository extends BaseRepository
{
    public function __construct(OTP $model)
    {
        parent::__construct($model);
    }

    protected function getCachePrefix(): string
    {
        return 'otps';
    }

    public function findByReference(string $reference): ?OTP
    {
        return $this->findBy(['reference' => $reference]);
    }

    public function findValidOTP(string $mobile, string $reference): ?OTP
    {
        return $this->model
            ->where('mobile', $mobile)
            ->where('reference', $reference)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function createOTP(string $mobile, string $purpose): OTP
    {
        return $this->create([
            'mobile' => $mobile,
            'code' => $this->generateCode(),
            'reference' => $this->generateReference(),
            'purpose' => $purpose,
            'attempts' => 0,
            'max_attempts' => config('auth.otp.max_attempts', 3),
            'expires_at' => now()->addMinutes(config('auth.otp.ttl', 5)),
        ]);
    }

    public function markAsVerified(int $id): void
    {
        $this->update($id, ['verified_at' => now()]);
    }

    public function revokeExistingOTPs(string $mobile, string $purpose): void
    {
        $this->model
            ->where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateReference(): string
    {
        return 'OTP-' . uniqid();
    }
}
