<?php

declare(strict_types=1);

namespace App\Modules\Sales\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OrderCancellationException extends Exception
{
    public function __construct(string $message = 'Order cannot be cancelled.')
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
