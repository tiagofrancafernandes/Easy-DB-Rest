<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\SupportedDriver;
use App\Models\Connection;

final class ConnectionConfigDto
{
    public function __construct(
        public readonly SupportedDriver $driver,
        public readonly ?string $database = null,
        public readonly ?string $host = null,
        public readonly ?int $port = null,
        public readonly ?string $username = null,
        public readonly ?string $password = null,
        public readonly ?string $schema = null,
        public readonly ?int $timeout = null,
        public readonly ?array $options = null,
        public readonly ?string $url = null,
        public readonly ?string $charset = null,
        public readonly ?string $collation = null,
        public readonly ?string $prefix = null,
        public readonly ?string $search_path = null,
        public readonly ?string $sslmode = null,
    ) {
    }

    public static function fromModel(Connection $connection): self
    {
        return new self(
            driver:      SupportedDriver::from($connection->driver),
            database:    $connection->database,
            host:        $connection->host,
            port:        $connection->port,
            username:    $connection->username,
            password:    $connection->password,
            schema:      $connection->schema,
            timeout:     $connection->timeout,
            options:     $connection->options,
            url:         $connection->url,
            charset:     $connection->charset,
            collation:   $connection->collation,
            prefix:      $connection->prefix,
            search_path: $connection->search_path,
            sslmode:     $connection->sslmode,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $driver = SupportedDriver::from($data['driver']);

        $password = isset($data['password'])
            ? base64_decode($data['password'], strict: true) ?: $data['password']
            : null;

        return new self(
            driver:      $driver,
            database:    $data['database'] ?? null,
            host:        $data['host'] ?? null,
            port:        isset($data['port']) ? (int) $data['port'] : $driver->defaultPort(),
            username:    $data['username'] ?? null,
            password:    $password,
            schema:      $data['schema'] ?? null,
            timeout:     isset($data['timeout']) ? (int) $data['timeout'] : null,
            options:     $data['options'] ?? null,
            url:         $data['url'] ?? null,
            charset:     $data['charset'] ?? null,
            collation:   $data['collation'] ?? null,
            prefix:      $data['prefix'] ?? null,
            search_path: $data['search_path'] ?? null,
            sslmode:     $data['sslmode'] ?? null,
        );
    }

    /**
     * Produces a Laravel database config array for Config::set().
     *
     * @return array<string, mixed>
     */
    public function toLaravelConfig(): array
    {
        $base = [
            'driver' => $this->driver->value,
        ];

        if ($this->url) {
            $base['url'] = $this->url;
        }

        if ($this->database) {
            $base['database'] = $this->database;
        }

        if ($this->prefix) {
            $base['prefix'] = $this->prefix;
        }

        if ($this->driver === SupportedDriver::Sqlite) {
            return $base;
        }

        $config = array_merge($base, [
            'host'      => $this->host,
            'port'      => $this->port ?? $this->driver->defaultPort(),
            'username'  => $this->username,
            'password'  => $this->password,
            'charset'   => $this->charset ?? 'utf8',
            'schema'    => $this->schema,
            'options'   => $this->options ?? [],
        ]);

        if ($this->collation) {
            $config['collation'] = $this->collation;
        }

        if ($this->search_path) {
            $config['search_path'] = $this->search_path;
        }

        if ($this->sslmode) {
            $config['sslmode'] = $this->sslmode;
        }

        return $config;
    }

    public function resolvedTimeout(): int
    {
        return $this->timeout ?? (int) config('application.query.default_timeout', 30);
    }
}
