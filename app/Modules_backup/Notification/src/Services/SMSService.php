<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\SMSServiceInterface;
use App\Modules\Notification\Repositories\SMSLogRepository;

class SMSService implements SMSServiceInterface
{
    public function __construct(
        private SMSLogRepository $logRepository
    ) {
    }

    public function send(string $mobile, string $template, array $data): void
    {
        $message = $this->getMessageForTemplate($template, $data);

        // Send via configured SMS provider (Twilio, etc.)
        // Implementation depends on the SMS provider

        $this->logRepository->create([
            'mobile' => $mobile,
            'template' => $template,
            'message' => $message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function sendOTP(string $mobile, string $code): void
    {
        $this->send($mobile, 'otp', ['code' => $code]);
    }

    public function sendOrderConfirmation(string $mobile, string $orderNumber): void
    {
        $this->send($mobile, 'order_confirmation', ['order_number' => $orderNumber]);
    }

    private function getMessageForTemplate(string $template, array $data): string
    {
        $templates = [
            'otp' => "Your OTP code is: {code}",
            'order_confirmation' => "Your order {order_number} has been confirmed. Thank you for shopping with us!",
            'order_shipped' => "Your order {order_number} has been shipped. Track your order at: {tracking_url}",
            'order_delivered' => "Your order {order_number} has been delivered. Enjoy your purchase!",
            'payment_received' => "Payment received for order {order_number}. Amount: {amount}",
        ];

        $message = $templates[$template] ?? '';

        foreach ($data as $key => $value) {
            $message = str_replace("{{$key}}", (string) $value, $message);
        }

        return $message;
    }
}
