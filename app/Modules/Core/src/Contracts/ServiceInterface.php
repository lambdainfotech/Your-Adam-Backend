<?php

declare(strict_types=1);

namespace App\Modules\Core\Contracts;

interface ServiceInterface
{
    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commit a database transaction.
     */
    public function commit(): void;

    /**
     * Rollback a database transaction.
     */
    public function rollback(): void;

    /**
     * Execute a callback within a transaction.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function transaction(callable $callback);
}
