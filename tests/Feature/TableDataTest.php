<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TableDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = database_path('data_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);
    }

    protected function tearDown(): void
    {
        $dbPath = database_path('data_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        parent::tearDown();
    }

    #[Test]
    public function itCanInsertAndRetrieveTableData(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('data_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // 1. Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'users_data',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
            ],
        ])->assertCreated();

        // 2. Insert record
        $this->postJson("/api/connections/{$connection->id}/tables/users_data/data", [
            'data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ])->assertCreated();

        // 3. Retrieve data
        $response = $this->getJson("/api/connections/{$connection->id}/tables/users_data/data");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Doe')
            ->assertJsonPath('data.0.email', 'john@example.com')
            ->assertJsonPath('meta.total', 1);
    }

    #[Test]
    public function itHandlesPagination(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('data_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'logs',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'message', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert 3 records
        for ($i = 1; $i <= 3; $i++) {
            $this->postJson("/api/connections/{$connection->id}/tables/logs/data", [
                'data' => ['message' => "Log {$i}"],
            ])->assertCreated();
        }

        // Fetch with limit 2
        $response = $this->getJson("/api/connections/{$connection->id}/tables/logs/data?limit=2");
        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3);

        // Fetch offset 2
        $response = $this->getJson("/api/connections/{$connection->id}/tables/logs/data?limit=2&offset=2");
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.message', 'Log 3');
    }
}
