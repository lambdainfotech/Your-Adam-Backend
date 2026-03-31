<?php

declare(strict_types=1);

namespace App\Modules\Notification\Contracts;

interface EmailServiceInterface
{
    public function send(
        string $to,
        string $template,
        array $data,
        ?string $subject = null
    ): void;

    public function sendOrderConfirmation(int $orderId): void;

    public function sendOrderStatusUpdate(int $orderId, string $status): void;
}
