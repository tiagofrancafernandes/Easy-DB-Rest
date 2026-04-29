<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\ConnectionConfigDto;
use App\Enums\SupportedDriver;
use App\Exceptions\ConnectionException;
use App\Models\Connection;
use App\Services\ConnectionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConnectionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected ConnectionManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = app(ConnectionManager::class);
    }

    #[Test]
    public function itResolvesConfigFromStoredConnection(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'host' => null,
            'port' => null,
        ]);

        $config = $this->manager->resolveConfig($connection->id);

        $this->assertInstanceOf(ConnectionConfigDto::class, $config);
        $this->assertEquals(SupportedDriver::Sqlite, $config->driver);
        $this->assertEquals(database_path('database.sqlite'), $config->database);
    }

    #[Test]
    public function itThrowsExceptionWhenConfigIdNotFound(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->manager->resolveConfig('non-existent-uuid');
    }

    #[Test]
    public function itResolvesConfigWithOverrides(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'port' => null,
        ]);

        $overrides = [
            'database' => database_path('other.sqlite'),
            'port' => 5432,
        ];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        $this->assertEquals(database_path('other.sqlite'), $config->database);
        $this->assertEquals(5432, $config->port);
    }

    #[Test]
    public function itResolvesFromInlineConfig(): void
    {
        $inlineConfig = [
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ];

        $connection = $this->manager->resolveFromRequest(
            configId: null,
            inlineConfig: $inlineConfig,
        );

        $this->assertNotNull($connection);
        $this->assertEquals('sqlite', $connection->getDriverName());
    }

    #[Test]
    public function itThrowsExceptionWhenNoConfigProvided(): void
    {
        $this->expectException(ConnectionException::class);

        $this->manager->resolveFromRequest();
    }

    #[Test]
    public function itDecryptsJWTPasswordOverride(): void
    {
        $password = 'my-secret-password';
        $encrypted = \App\Helpers\StringHelpers::modelEncrypt($password);

        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $overrides = ['password' => $encrypted];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        $this->assertEquals($password, $config->password);
    }

    #[Test]
    public function itDecodesBase64PasswordOverride(): void
    {
        $password = 'my-secret-password';
        $encoded = base64_encode($password);

        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $overrides = ['password' => $encoded];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        $this->assertEquals($password, $config->password);
    }

    #[Test]
    public function itUsesRawPasswordIfNotEncryptedOrBase64(): void
    {
        $password = 'plain-password-123';

        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $overrides = ['password' => $password];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        $this->assertEquals($password, $config->password);
    }

    #[Test]
    public function itHandlesEmptyPasswordOverride(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'password' => 'original-password',
        ]);

        // When password override is whitespace, it's treated as-is
        // (fails base64 decode, returns the whitespace string)
        $overrides = ['password' => '   '];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        // The override is applied even if it's just whitespace
        $this->assertEquals('   ', $config->password);
    }

    #[Test]
    public function itResolvesFromDtoDirectly(): void
    {
        $dto = new ConnectionConfigDto(
            driver: SupportedDriver::Sqlite,
            database: database_path('database.sqlite'),
            host: null,
            port: null,
            username: null,
            password: null,
            schema: null,
            timeout: 30,
            options: null,
            url: null,
            charset: null,
            collation: null,
            prefix: null,
            search_path: null,
            sslmode: null,
        );

        $connection = $this->manager->resolveFromDto($dto);

        $this->assertNotNull($connection);
        $this->assertEquals('sqlite', $connection->getDriverName());
    }

    #[Test]
    public function itThrowsExceptionForDisallowedDriver(): void
    {
        config(['application.connections.allowed_drivers' => ['sqlite']]);

        // Create a new manager instance with the updated config
        $manager = app(ConnectionManager::class);

        $this->expectException(ConnectionException::class);

        $dto = new ConnectionConfigDto(
            driver: SupportedDriver::Pgsql,
            database: 'testdb',
            host: 'localhost',
            port: 5432,
            username: 'postgres',
            password: 'secret',
            schema: null,
            timeout: 30,
            options: null,
            url: null,
            charset: null,
            collation: null,
            prefix: null,
            search_path: null,
            sslmode: null,
        );

        $manager->resolveFromDto($dto);
    }

    #[Test]
    public function itPreservesNullPasswordWhenNoOverride(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
            'password' => null,
        ]);

        $config = $this->manager->resolveConfig($connection->id);

        $this->assertNull($config->password);
    }

    #[Test]
    public function itAppliesAllConfigOverrides(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'mysql',
            'host' => 'original-host',
            'port' => 3306,
            'database' => 'original_db',
            'username' => 'original_user',
            'password' => 'original_pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'schema' => null,
            'timeout' => 30,
            'options' => ['a' => 1],
        ]);

        $overrides = [
            'host' => 'new-host',
            'port' => 3307,
            'database' => 'new_db',
            'username' => 'new_user',
            'password' => 'new_pass',
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => 'prefix_',
            'schema' => 'public',
            'timeout' => 60,
            'options' => ['b' => 2],
        ];

        $config = $this->manager->resolveConfig($connection->id, $overrides);

        $this->assertEquals('new-host', $config->host);
        $this->assertEquals(3307, $config->port);
        $this->assertEquals('new_db', $config->database);
        $this->assertEquals('new_user', $config->username);
        $this->assertEquals('new_pass', $config->password);
        $this->assertEquals('utf8', $config->charset);
        $this->assertEquals('utf8_general_ci', $config->collation);
        $this->assertEquals('prefix_', $config->prefix);
        $this->assertEquals('public', $config->schema);
        $this->assertEquals(60, $config->timeout);
        $this->assertEquals(['b' => 2], $config->options);
    }
}
