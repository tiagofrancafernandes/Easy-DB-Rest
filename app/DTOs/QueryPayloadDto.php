<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\QueryType;

final class QueryPayloadDto
{
    /**
     * @param array<int, mixed>|string $query
     * @param array<mixed>             $bindings
     * @param array<mixed>|null        $execute
     */
    public function __construct(
        public readonly QueryType $type,
        public readonly array|string $query,
        public readonly array $bindings = [],
        public readonly ?array $execute = null,
        public readonly ?string $table = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type:     QueryType::from($data['type']),
            query:    $data['query'],
            bindings: $data['bindings'] ?? [],
            execute:  $data['execute'] ?? null,
            table:    $data['table'] ?? null,
        );
    }

    public static function fromRawSql(string $sql): self
    {
        return new self(
            type:  QueryType::Raw,
            query: $sql,
        );
    }

    public function isRaw(): bool
    {
        return $this->type === QueryType::Raw;
    }
}
