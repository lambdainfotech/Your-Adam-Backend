<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\AuthServiceInterface;
use App\Modules\Auth\DTOs\LoginDTO;
use App\Modules\Auth\DTOs\RegisterDTO;
use App\Modules\Auth\DTOs\TokenDTO;
use App\Modules\Auth\Enums\UserStatus;
use App\Models\User;
use App\Modules\Auth\Repositories\OTPRepository;
use App\Modules\Auth\Repositories\RoleRepository;
use App\Modules\Auth\Repositories\UserRepository;
use App\Modules\Core\Abstracts\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService extends BaseService implements AuthServiceInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private OTPRepository $otpRepository,
        private JWTService $jwtService
    ) {
    }

    public function login(LoginDTO $dto): array
    {
        $user = $this->userRepository->findByMobile($dto->mobile);

        if (!$user) {
            throw new \RuntimeException('Invalid credentials');
        }

        if ($user->status === UserStatus::SUSPENDED) {
            throw new \RuntimeException('Account is suspended');
        }

        if (!Hash::check($dto->password, $user->password)) {
            throw new \RuntimeException('Invalid credentials');
        }

        // Update last login
        $this->userRepository->updateLastLogin($user->id);

        $tokens = $this->jwtService->generateTokens($user);

        return ['user' => $user, 'tokens' => $tokens];
    }

    public function register(RegisterDTO $dto): array
    {
        return $this->transaction(function () use ($dto) {
            $role = $this->roleRepository->getDefaultRole();

            $user = $this->userRepository->createWithProfile([
                'mobile' => $dto->mobile,
                'password' => Hash::make($dto->password),
                'role_id' => $role->id,
                'status' => UserStatus::ACTIVE,
                'mobile_verified_at' => now(),
            ], [
                'full_name' => $dto->fullName,
                'email' => $dto->email,
            ]);

            $tokens = $this->jwtService->generateTokens($user);

            return ['user' => $user, 'tokens' => $tokens];
        });
    }

    public function logout(): void
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Exception $e) {
            // Token might already be invalid, ignore
        }

        Auth::logout();
    }

    public function refreshToken(string $refreshToken): TokenDTO
    {
        return $this->jwtService->refreshAccessToken($refreshToken);
    }

    public function getCurrentUser(): ?User
    {
        try {
            return Auth::user();
        } catch (\Exception $e) {
            return null;
        }
    }
}
