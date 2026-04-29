<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use App\DTOs\QueryPayloadDto;

class QueryExecutorService
{
    public function __construct(
        protected readonly ConnectionManager $connectionManager,
        protected readonly QuerySecurityGuard $securityGuard,
        protected readonly RawQueryExecutor $rawExecutor,
        protected readonly BuilderQueryExecutor $builderExecutor,
    ) {
    }

    public function execute(
        QueryPayloadDto $payload,
        ?string $configId,
        ?array $inlineConnection,
        array $overrides = [],
    ): array {
        $this->securityGuard->validate($payload);

        $connection = $this->connectionManager->resolveFromRequest($configId, $inlineConnection, $overrides);
        $configDto  = $this->buildConfigDto($configId, $inlineConnection, $overrides);

        if ($payload->isRaw()) {
            return $this->rawExecutor->execute($connection, $configDto, (string) $payload->query, $payload->bindings);
        }

        return $this->builderExecutor->execute($connection, $configDto, $payload);
    }

    public function testConnection(?string $configId = null, ?array $inlineConnection = null, array $overrides = []): bool
    {
        $connection = $this->connectionManager->resolveFromRequest($configId, $inlineConnection, $overrides);

        $connection->getPdo();

        return true;
    }

    protected function buildConfigDto(?string $configId, ?array $inlineConnection, array $overrides): ConnectionConfigDto
    {
        if ($configId !== null) {
            $model = \App\Models\Connection::findOrFail($configId);
            $dto   = ConnectionConfigDto::fromModel($model);

            if (empty($overrides)) {
                return $dto;
            }

            return ConnectionConfigDto::fromArray(array_merge($dto->toLaravelConfig(), $overrides));
        }

        return ConnectionConfigDto::fromArray(array_merge($inlineConnection ?? [], $overrides));
    }
}
