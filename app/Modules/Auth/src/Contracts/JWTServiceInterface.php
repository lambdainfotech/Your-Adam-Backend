<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\DTOs\TokenDTO;
use App\Models\User;

interface JWTServiceInterface
{
    /**
     * Generate access and refresh tokens for the user.
     *
     * @param User $user
     * @return TokenDTO
     */
    public function generateTokens(User $user): TokenDTO;

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshToken
     * @return TokenDTO
     */
    public function refreshAccessToken(string $refreshToken): TokenDTO;

    /**
     * Invalidate the given token.
     *
     * @param string $token
     */
    public function invalidateToken(string $token): void;

    /**
     * Decode the token and return its payload.
     *
     * @param string $token
     * @return array
     */
    public function decodeToken(string $token): array;
}
