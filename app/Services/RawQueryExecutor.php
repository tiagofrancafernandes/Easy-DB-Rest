<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;

class RawQueryExecutor
{
    public function execute(
        Connection $connection,
        ConnectionConfigDto $config,
        string $sql,
        array $bindings = [],
    ): array {
        $timeout = $config->resolvedTimeout();
        $start   = hrtime(true);

        $rows = $this->runWithTimeout($connection, $sql, $bindings, $timeout);

        $elapsed = $this->elapsedMs($start);

        Log::debug('RawQueryExecutor: query executed', [
            'driver'         => $config->driver->value,
            'execution_time' => $elapsed,
            'rows'           => count($rows),
        ]);

        return [
            'data' => $rows,
            'meta' => [
                'execution_time' => $elapsed,
                'rows'           => count($rows),
                'driver'         => $config->driver->value,
            ],
        ];
    }

    private function runWithTimeout(
        Connection $connection,
        string $sql,
        array $bindings,
        int $timeout,
    ): array {
        $previous = ini_set('max_execution_time', (string) $timeout);

        try {
            return $connection->select($sql, $bindings);
        } finally {
            ini_set('max_execution_time', $previous !== false ? $previous : '0');
        }
    }

    private function elapsedMs(int|float $startNano): string
    {
        $elapsed = (hrtime(true) - $startNano) / 1_000_000;

        return round($elapsed, 3) . 'ms';
    }
}
