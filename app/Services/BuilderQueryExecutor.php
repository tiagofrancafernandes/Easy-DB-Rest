<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use App\DTOs\QueryPayloadDto;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class BuilderQueryExecutor
{
    private const TERMINAL_METHODS = ['get', 'first', 'value', 'pluck', 'count', 'exists', 'doesntExist'];

    public function execute(
        Connection $connection,
        ConnectionConfigDto $config,
        QueryPayloadDto $payload,
    ): array {
        $start = hrtime(true);

        $builder = $this->buildQuery($connection, $payload);
        $rows    = $this->runTerminal($builder, $payload->execute);
        $elapsed = $this->elapsedMs($start);

        Log::debug('BuilderQueryExecutor: query executed', [
            'driver'         => $config->driver->value,
            'execution_time' => $elapsed,
        ]);

        $normalized = $this->normalizeRows($rows);

        return [
            'data' => $normalized,
            'meta' => [
                'execution_time' => $elapsed,
                'rows'           => count($normalized),
                'driver'         => $config->driver->value,
            ],
        ];
    }

    private function buildQuery(Connection $connection, QueryPayloadDto $payload): Builder
    {
        $builder = $connection->table($payload->table ?? '');

        foreach ((array) $payload->query as $step) {
            $method = (string) ($step['method'] ?? $step[0] ?? '');
            $args   = (array) ($step['args'] ?? $step[1] ?? []);

            if (empty($method)) {
                continue;
            }

            $builder = $builder->{$method}(...$args);
        }

        return $builder;
    }

    private function runTerminal(Builder $builder, ?array $executeDirective): mixed
    {
        if ($executeDirective === null) {
            return $builder->get()->toArray();
        }

        $method = (string) ($executeDirective['method'] ?? $executeDirective[0] ?? 'get');
        $args   = (array) ($executeDirective['args'] ?? $executeDirective[1] ?? []);

        if (!in_array($method, static::TERMINAL_METHODS, strict: true)) {
            return $builder->get()->toArray();
        }

        return $builder->{$method}(...$args);
    }

    private function normalizeRows(mixed $rows): array
    {
        if (is_array($rows)) {
            return $rows;
        }

        if ($rows instanceof \Illuminate\Support\Collection) {
            return $rows->toArray();
        }

        return [$rows];
    }

    private function elapsedMs(int|float $startNano): string
    {
        $elapsed = (hrtime(true) - $startNano) / 1_000_000;

        return round($elapsed, 3) . 'ms';
    }
}
