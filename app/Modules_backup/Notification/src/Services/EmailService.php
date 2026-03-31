<?php

declare(strict_types=1);

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\EmailServiceInterface;
use App\Modules\Notification\Repositories\EmailLogRepository;
use App\Modules\Order\Models\Order;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\View;

class EmailService implements EmailServiceInterface
{
    public function __construct(
        private Mailer $mailer,
        private EmailLogRepository $logRepository
    ) {
    }

    public function send(
        string $to,
        string $template,
        array $data,
        ?string $subject = null
    ): void {
        $view = "emails.{$template}";

        $this->mailer->send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });

        $this->logRepository->create([
            'to_email' => $to,
            'template' => $template,
            'subject' => $subject,
            'body' => view($view, $data)->render(),
            'data' => $data,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function sendOrderConfirmation(int $orderId): void
    {
        $order = Order::with('user')->find($orderId);

        if (!$order || !$order->user) {
            return;
        }

        $this->send(
            $order->user->email,
            'order_confirmation',
            ['order' => $order],
            "Order Confirmation - #{$order->order_number}"
        );
    }

    public function sendOrderStatusUpdate(int $orderId, string $status): void
    {
        $order = Order::with('user')->find($orderId);

        if (!$order || !$order->user) {
            return;
        }

        $this->send(
            $order->user->email,
            'order_status_update',
            ['order' => $order, 'status' => $status],
            "Order Status Update - #{$order->order_number}"
        );
    }
}
