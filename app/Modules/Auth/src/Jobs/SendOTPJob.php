<?php

declare(strict_types=1);

namespace App\Modules\Auth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOTPJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $mobile,
        public string $code
    ) {
    }

    public function handle(): void
    {
        // TODO: Replace with actual SMS gateway integration (e.g., Twilio, MessageBird, local provider)
        // Example:
        // $smsService = app(SMSService::class);
        // $smsService->send($this->mobile, "Your verification code is: {$this->code}");
        
        // For now, log the OTP so it can be retrieved during development
        \Log::info("OTP dispatched", [
            'mobile' => $this->mobile,
            'code' => $this->code,
        ]);
    }
}
