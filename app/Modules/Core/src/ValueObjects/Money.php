<?php

declare(strict_types=1);

namespace App\Modules\Core\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        private int $amount, // Amount in smallest currency unit (cents/paisa)
        private string $currency = 'BDT'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    /**
     * Create from decimal amount.
     */
    public static function fromDecimal(float $amount, string $currency = 'BDT'): self
    {
        return new self(
            amount: (int) round($amount * 100),
            currency: $currency
        );
    }

    /**
     * Get decimal amount.
     */
    public function toDecimal(): float
    {
        return $this->amount / 100;
    }

    /**
     * Get formatted amount for display.
     */
    public function format(): string
    {
        $amount = number_format($this->toDecimal(), 2);
        return "{$this->currency} {$amount}";
    }

    /**
     * Add two money values.
     */
    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        return new self(
            amount: $this->amount + $other->amount,
            currency: $this->currency
        );
    }

    /**
     * Subtract two money values.
     */
    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        if ($other->amount > $this->amount) {
            throw new InvalidArgumentException('Insufficient amount');
        }
        
        return new self(
            amount: $this->amount - $other->amount,
            currency: $this->currency
        );
    }

    /**
     * Multiply by a factor.
     */
    public function multiply(float $factor): self
    {
        return new self(
            amount: (int) round($this->amount * $factor),
            currency: $this->currency
        );
    }

    /**
     * Calculate percentage.
     */
    public function percentage(float $percent): self
    {
        return new self(
            amount: (int) round($this->amount * $percent / 100),
            currency: $this->currency
        );
    }

    /**
     * Check if two money values are equal.
     */
    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }

    /**
     * Get raw amount (in smallest unit).
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Get currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Ensure currencies match.
     */
    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}
