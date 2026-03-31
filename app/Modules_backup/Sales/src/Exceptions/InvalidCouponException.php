<?php

declare(strict_types=1);

namespace App\Modules\Sales\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InvalidCouponException extends Exception
{
    public function __construct(string $message = 'Invalid or expired coupon code.')
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
