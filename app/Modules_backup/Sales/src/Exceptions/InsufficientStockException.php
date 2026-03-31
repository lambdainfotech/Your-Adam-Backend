<?php

declare(strict_types=1);

namespace App\Modules\Sales\Exceptions;

use Exception;
use Illuminate\Http\Response;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = 'Insufficient stock available.')
    {
        parent::__construct($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
