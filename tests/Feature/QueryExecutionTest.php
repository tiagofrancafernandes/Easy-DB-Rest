<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use Database\Seeders\SmokeTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the testing DB so we have tables to query
        $this->seed(SmokeTestSeeder::class);
    }

    protected function getRuntimeConfig(): array
    {
        return [
            'name'     => 'Runtime DB',
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
        ];
    }

    #[Test]
    public function itExecutesRawSqlUsingRuntimeConfig(): void
    {
        $response = $this->postJson('/api/query', [
            'type' => 'raw',
            'query' => 'SELECT name, price FROM products ORDER BY name ASC',
            'connection' => $this->getRuntimeConfig()
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Deluxe Kit')
            ->assertJsonPath('data.0.price', 99.99)
            ->assertJsonPath('data.1.name', 'Gadget Pro');
    }

    #[Test]
    public function itExecutesRawSqlFromRawBody(): void
    {
        // Must send runtime connection as query parameter since body is raw text
        $config = $this->getRuntimeConfig();

        $response = $this->call(
            'POST',
            '/api/query',
            ['connection' => $config],
            [],
            [],
            [
                'CONTENT_TYPE' => 'text/x-sql',
                'HTTP_ACCEPT' => 'application/json',
            ],
            'SELECT name FROM products ORDER BY name ASC LIMIT 1'
        );

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Deluxe Kit');
    }

    #[Test]
    public function itExecutesBuilderQueries(): void
    {
        $response = $this->postJson('/api/query', [
            'type' => 'select',
            'table' => 'orders',
            'connection' => $this->getRuntimeConfig(),
            'query' => [
                [
                    'method' => 'select',
                    'args' => ['id', 'product_id', 'total']
                ],
                [
                    'method' => 'where',
                    'args' => ['total', '>', 50]
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['total', 'desc']
                ],
                [
                    'method' => 'limit',
                    'args' => [1]
                ]
            ]
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.product_id', 5)
            ->assertJsonPath('data.0.total', 99.99);
    }

    #[Test]
    public function itTestsAConnectionSuccessfully(): void
    {
        $response = $this->postJson('/api/connection/test', [
            'connection' => $this->getRuntimeConfig(),
        ]);

        $response->assertOk()
            ->assertJsonPath('connected', true)
            ->assertJsonPath('message', 'Connection established successfully.');
    }

    #[Test]
    public function itFailsToConnectWithBadConfig(): void
    {
        $badConfig = $this->getRuntimeConfig();
        // For sqlite, Laravel checks file existence before PDO connect and throws InvalidArgumentException
        $badConfig['database'] = '/path/to/nowhere.sqlite';

        $response = $this->postJson('/api/connection/test', [
            'connection' => $badConfig,
        ]);

        $response->assertStatus(500);
    }

    #[Test]
    public function itUsesPersistedConfigFromHeader(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $responseRaw = $this->call(
            'POST',
            '/api/query',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'text/x-sql',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONFIG_ID' => $connection->id,
            ],
            'SELECT name FROM products ORDER BY name ASC LIMIT 1'
        );

        $responseRaw->assertOk()
            ->assertJsonPath('data.0.name', 'Deluxe Kit');
    }
}
