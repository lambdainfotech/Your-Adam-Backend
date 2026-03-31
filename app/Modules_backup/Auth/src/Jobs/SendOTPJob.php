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
        // OTP sending logic will be implemented here
        // This is a placeholder for the actual SMS service integration
    }
}
