<?php

declare(strict_types=1);

namespace App\Modules\Core\Abstracts;

use App\Modules\Core\Contracts\ServiceInterface;
use Illuminate\Support\Facades\DB;

abstract class BaseService implements ServiceInterface
{
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollback(): void
    {
        DB::rollBack();
    }

    public function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }

    /**
     * Execute callback with transaction retry on deadlock.
     *
     * @template T
     * @param callable(): T $callback
     * @param int $attempts
     * @return T
     */
    public function transactionWithRetry(callable $callback, int $attempts = 3)
    {
        return DB::transaction($callback, $attempts);
    }
}
