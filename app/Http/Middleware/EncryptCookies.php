<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * Cookie Encryption Middleware
 * 
 * This middleware encrypts/decrypts all cookies including the JWT token.
 * We let Laravel handle the encryption automatically.
 */
class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Let Laravel encrypt all cookies including jwt_token
    ];
}
