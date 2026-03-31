<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\JWTServiceInterface;
use App\Modules\Auth\DTOs\TokenDTO;
use App\Modules\Auth\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;

class JWTService implements JWTServiceInterface
{
    public function generateTokens(User $user): TokenDTO
    {
        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = JWTAuth::customClaims(['type' => 'refresh'])->fromUser($user);

        return new TokenDTO(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresIn: config('jwt.ttl') * 60
        );
    }

    public function refreshAccessToken(string $refreshToken): TokenDTO
    {
        try {
            JWTAuth::setToken($refreshToken);
            
            $payload = JWTAuth::getPayload();
            
            if ($payload->get('type') !== 'refresh') {
                throw new \InvalidArgumentException('Invalid token type');
            }

            $user = JWTAuth::authenticate();

            if (!$user) {
                throw new \RuntimeException('User not found');
            }

            // Invalidate the old refresh token
            JWTAuth::invalidate($refreshToken);

            // Generate new tokens
            return $this->generateTokens($user);
        } catch (TokenExpiredException $e) {
            throw new \RuntimeException('Refresh token has expired', 0, $e);
        } catch (TokenInvalidException $e) {
            throw new \RuntimeException('Invalid refresh token', 0, $e);
        } catch (JWTException $e) {
            throw new \RuntimeException('Could not refresh token', 0, $e);
        }
    }

    public function invalidateToken(string $token): void
    {
        try {
            JWTAuth::invalidate($token);
        } catch (JWTException $e) {
            // Token might already be invalid, ignore
        }
    }

    public function decodeToken(string $token): array
    {
        try {
            JWTAuth::setToken($token);
            return (array) JWTAuth::getPayload();
        } catch (JWTException $e) {
            throw new \RuntimeException('Could not decode token', 0, $e);
        }
    }
}
