<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum OTPPurpose: string
{
    case REGISTRATION = 'registration';
    case LOGIN = 'login';
    case PASSWORD_RESET = 'password_reset';
}
