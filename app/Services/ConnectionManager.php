<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use App\Enums\SupportedDriver;
use App\Exceptions\ConnectionException;
use App\Models\Connection;
use Illuminate\Database\Connection as DbConnection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConnectionManager
{
    /**
     * @var list<string>
     */
    private readonly array $allowedDrivers;

    public function __construct()
    {
        $this->allowedDrivers = config('application.connections.allowed_drivers', []);
    }

    public function resolveFromRequest(
        ?string $configId,
        ?array $inlineConfig,
        array $overrides = [],
    ): DbConnection {
        $dto = $this->buildDto($configId, $inlineConfig, $overrides);

        $this->guardAllowedDriver($dto->driver);

        return $this->register($dto);
    }

    public function resolveFromDto(ConnectionConfigDto $dto): DbConnection
    {
        $this->guardAllowedDriver($dto->driver);

        return $this->register($dto);
    }

    private function buildDto(
        ?string $configId,
        ?array $inlineConfig,
        array $overrides,
    ): ConnectionConfigDto {
        if ($configId !== null) {
            $connection = Connection::findOrFail($configId);
            $dto        = ConnectionConfigDto::fromModel($connection);

            return $this->applyOverrides($dto, $overrides);
        }

        if ($inlineConfig !== null) {
            $merged = array_merge($inlineConfig, $overrides);

            return ConnectionConfigDto::fromArray($merged);
        }

        throw ConnectionException::connectionFailed('No connection config ID or inline config provided.');
    }

    private function applyOverrides(ConnectionConfigDto $dto, array $overrides): ConnectionConfigDto
    {
        if (empty($overrides)) {
            return $dto;
        }

        $password = isset($overrides['password'])
            ? (base64_decode($overrides['password'], strict: true) ?: $overrides['password'])
            : $dto->password;

        return new ConnectionConfigDto(
            driver:      $dto->driver,
            database:    $overrides['database'] ?? $dto->database,
            host:        $overrides['host'] ?? $dto->host,
            port:        isset($overrides['port']) ? (int) $overrides['port'] : $dto->port,
            username:    $overrides['username'] ?? $dto->username,
            password:    $password,
            schema:      $overrides['schema'] ?? $dto->schema,
            timeout:     isset($overrides['timeout']) ? (int) $overrides['timeout'] : $dto->timeout,
            options:     $overrides['options'] ?? $dto->options,
            url:         $overrides['url'] ?? $dto->url,
            charset:     $overrides['charset'] ?? $dto->charset,
            collation:   $overrides['collation'] ?? $dto->collation,
            prefix:      $overrides['prefix'] ?? $dto->prefix,
            search_path: $overrides['search_path'] ?? $dto->search_path,
            sslmode:     $overrides['sslmode'] ?? $dto->sslmode,
        );
    }

    private function guardAllowedDriver(SupportedDriver $driver): void
    {
        if (in_array($driver->value, $this->allowedDrivers, strict: true)) {
            return;
        }

        throw ConnectionException::driverNotAllowed($driver->value);
    }

    private function register(ConnectionConfigDto $dto): DbConnection
    {
        $name = 'dynamic_' . Str::uuid()->toString();

        Config::set("database.connections.{$name}", $dto->toLaravelConfig());

        return DB::connection($name);
    }
}
