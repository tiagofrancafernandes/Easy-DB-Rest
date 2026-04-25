<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\QueryPayloadDto;
use App\Exceptions\QuerySecurityException;

class QuerySecurityGuard
{
    /**
     * @var list<string>
     */
    protected readonly array $blockedStatements;

    /**
     * @var list<string>
     */
    protected readonly array $allowedBuilderMethods;

    protected readonly bool $blockDangerous;

    public function __construct()
    {
        $this->blockDangerous        = (bool) config('application.query.block_dangerous_statements', true);
        $this->blockedStatements     = array_map('strtoupper', config('application.query.blocked_statements', []));
        $this->allowedBuilderMethods = config('application.connections.allowed_builder_methods', []);
    }

    public function validate(QueryPayloadDto $payload): void
    {
        if ($payload->isRaw()) {
            $this->validateRawSql((string) $payload->query);

            return;
        }

        $this->validateBuilderSteps((array) $payload->query);
    }

    protected function validateRawSql(string $sql): void
    {
        if (!$this->blockDangerous) {
            return;
        }

        $normalised = strtoupper(trim($sql));

        foreach ($this->blockedStatements as $statement) {
            if (!str_starts_with($normalised, $statement)) {
                continue;
            }

            throw QuerySecurityException::blockedStatement($statement);
        }
    }

    /**
     * @param array<mixed> $steps
     */
    protected function validateBuilderSteps(array $steps): void
    {
        foreach ($steps as $step) {
            $method = (string) ($step['method'] ?? $step[0] ?? '');

            if (empty($method)) {
                continue;
            }

            if (in_array($method, $this->allowedBuilderMethods, strict: true)) {
                continue;
            }

            throw QuerySecurityException::forbiddenBuilderMethod($method);
        }
    }
}
