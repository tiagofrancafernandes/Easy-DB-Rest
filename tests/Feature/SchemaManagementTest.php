<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SchemaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = database_path('schema_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);
    }

    protected function tearDown(): void
    {
        $dbPath = database_path('schema_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        parent::tearDown();
    }

    #[Test]
    public function itListsTables(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('schema_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/connections/{$connection->id}/tables");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function itCreatesAndDropsATable(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('schema_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'test_table',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'title', 'type' => 'string', 'nullable' => true],
                ['name' => 'votes', 'type' => 'integer', 'default' => 0],
            ],
        ])->assertCreated();

        // Check details
        $this->getJson("/api/connections/{$connection->id}/tables/test_table")
            ->assertOk()
            ->assertJsonPath('data.columns.0.name', 'id')
            ->assertJsonPath('data.columns.1.name', 'title')
            ->assertJsonPath('data.columns.1.type', 'varchar')
            ->assertJsonPath('data.columns.2.name', 'votes');

        // Drop table
        $this->deleteJson("/api/connections/{$connection->id}/tables/test_table")
            ->assertOk();

        $response = $this->getJson("/api/connections/{$connection->id}/tables");
        $this->assertNotContains('test_table', $response->json('data'));
    }

    #[Test]
    public function itAltersATable(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('schema_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // 1. Create
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'alter_test',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
            ],
        ])->assertCreated();

        // 2. Add column
        $this->putJson("/api/connections/{$connection->id}/tables/alter_test", [
            'add' => [
                ['name' => 'new_col', 'type' => 'string', 'nullable' => true]
            ]
        ])->assertOk();

        $this->getJson("/api/connections/{$connection->id}/tables/alter_test")
            ->assertOk()
            ->assertJsonPath('data.columns.1.name', 'new_col');

        // 3. Rename column (SQLite support for rename is limited but Laravel handles it)
        $this->putJson("/api/connections/{$connection->id}/tables/alter_test", [
            'rename' => ['new_col' => 'renamed_col']
        ])->assertOk();

        $this->getJson("/api/connections/{$connection->id}/tables/alter_test")
            ->assertOk()
            ->assertJsonPath('data.columns.1.name', 'renamed_col');

        // 4. Drop column
        $this->putJson("/api/connections/{$connection->id}/tables/alter_test", [
            'drop' => ['renamed_col']
        ])->assertOk();

        $this->getJson("/api/connections/{$connection->id}/tables/alter_test")
            ->assertOk()
            ->assertJsonCount(1, 'data.columns');
    }

    #[Test]
    public function itCreatesAndDropsAView(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('schema_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // 1. Create base table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'base_table',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'name', 'type' => 'string'],
            ],
        ])->assertCreated();

        // 2. Create view
        $this->postJson("/api/connections/{$connection->id}/views", [
            'name' => 'active_view',
            'query' => 'SELECT * FROM base_table',
        ])->assertCreated();

        // 3. List views
        $this->getJson("/api/connections/{$connection->id}/views")
            ->assertOk()
            ->assertJsonFragment(['name' => 'active_view']);

        // 4. Drop view
        $this->deleteJson("/api/connections/{$connection->id}/views/active_view")
            ->assertOk();
    }
}
