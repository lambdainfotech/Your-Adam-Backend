<?php

declare(strict_types=1);

namespace App\Modules\Auth\Contracts;

use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\DTOs\TokenDTO;
use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Authenticate user and return user with tokens.
     *
     * @param LoginDTO $dto
     * @return array{user: User, tokens: TokenDTO}
     */
    public function login(LoginDTO $dto): array;

    /**
     * Register a new user and return user with tokens.
     *
     * @param RegisterDTO $dto
     * @return array{user: User, tokens: TokenDTO}
     */
    public function register(RegisterDTO $dto): array;

    /**
     * Logout the current user.
     */
    public function logout(): void;

    /**
     * Refresh access token using refresh token.
     *
     * @param string $refreshToken
     * @return TokenDTO
     */
    public function refreshToken(string $refreshToken): TokenDTO;

    /**
     * Get the currently authenticated user.
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User;
}
