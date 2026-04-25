<?php

declare(strict_types=1);

namespace App\Modules\Core\ValueObjects;

use InvalidArgumentException;

final readonly class MobileNumber
{
    private string $number;
    private string $countryCode;

    public function __construct(string $number)
    {
        $normalized = $this->normalize($number);
        
        if (!$this->isValid($normalized)) {
            throw new InvalidArgumentException('Invalid mobile number format');
        }
        
        $this->number = $normalized;
        $this->countryCode = $this->extractCountryCode($normalized);
    }

    /**
     * Create from input.
     */
    public static function from(string $number): self
    {
        return new self($number);
    }

    /**
     * Get formatted number with country code.
     */
    public function getFullNumber(): string
    {
        return $this->number;
    }

    /**
     * Get number without country code.
     */
    public function getNumber(): string
    {
        return substr($this->number, strlen($this->countryCode));
    }

    /**
     * Get country code.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Get masked number for display.
     */
    public function getMasked(): string
    {
        $length = strlen($this->number);
        $visible = 4; // Last 4 digits
        $hidden = $length - $visible - strlen($this->countryCode);
        
        return $this->countryCode . str_repeat('X', $hidden) . substr($this->number, -$visible);
    }

    /**
     * Check if two numbers are equal.
     */
    public function equals(self $other): bool
    {
        return $this->number === $other->number;
    }

    /**
     * Normalize mobile number.
     */
    private function normalize(string $number): string
    {
        // Remove all non-digit characters
        $number = preg_replace('/\D/', '', $number);
        
        // Handle local Bangladesh numbers
        if (strlen($number) === 11 && str_starts_with($number, '01')) {
            $number = '+88' . $number;
        }
        
        // Handle numbers starting with 880
        if (strlen($number) === 13 && str_starts_with($number, '880')) {
            $number = '+' . $number;
        }
        
        // Add + if missing
        if (!str_starts_with($number, '+')) {
            $number = '+' . $number;
        }
        
        return $number;
    }

    /**
     * Validate mobile number.
     */
    private function isValid(string $number): bool
    {
        // Bangladesh: +8801XXXXXXXXX (14 chars)
        // General: minimum 10 digits after country code
        if (strlen($number) < 10) {
            return false;
        }
        
        // Check Bangladesh format
        if (str_starts_with($number, '+880')) {
            return preg_match('/^\+8801[3-9]\d{8}$/', $number) === 1;
        }
        
        // Generic international format
        return preg_match('/^\+\d{10,15}$/', $number) === 1;
    }

    /**
     * Extract country code.
     */
    private function extractCountryCode(string $number): string
    {
        // Common country codes
        $codes = ['+880', '+91', '+1', '+44', '+61'];
        
        foreach ($codes as $code) {
            if (str_starts_with($number, $code)) {
                return $code;
            }
        }
        
        // Default: extract first 1-3 digits after +
        if (preg_match('/^(\+\d{1,3})/', $number, $matches)) {
            return $matches[1];
        }
        
        return '+';
    }
}
