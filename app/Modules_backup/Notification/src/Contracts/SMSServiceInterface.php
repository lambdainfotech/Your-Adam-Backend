<?php

declare(strict_types=1);

namespace App\Modules\Notification\Contracts;

interface SMSServiceInterface
{
    public function send(string $mobile, string $template, array $data): void;

    public function sendOTP(string $mobile, string $code): void;

    public function sendOrderConfirmation(string $mobile, string $orderNumber): void;
}
