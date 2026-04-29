<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TableDataControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = database_path('table_data_controller_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);
    }

    protected function tearDown(): void
    {
        $dbPath = database_path('table_data_controller_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        parent::tearDown();
    }

    #[Test]
    public function itListsTableDataWithPagination(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'users',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'name', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert data
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson("/api/connections/{$connection->id}/tables/users/data", [
                'data' => ['name' => "User {$i}"],
            ])->assertCreated();
        }

        // Test default pagination
        $response = $this->getJson("/api/connections/{$connection->id}/tables/users/data");

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 5);
    }

    #[Test]
    public function itLimitsPaginationResults(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'items',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'title', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert 10 records
        for ($i = 1; $i <= 10; $i++) {
            $this->postJson("/api/connections/{$connection->id}/tables/items/data", [
                'data' => ['title' => "Item {$i}"],
            ])->assertCreated();
        }

        // Test with limit
        $response = $this->getJson(
            "/api/connections/{$connection->id}/tables/items/data?limit=3"
        );

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 10);
    }

    #[Test]
    public function itUsesOffsetForPagination(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'products',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'sku', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert records
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson("/api/connections/{$connection->id}/tables/products/data", [
                'data' => ['sku' => "SKU-{$i}"],
            ])->assertCreated();
        }

        // Test with offset
        $response = $this->getJson(
            "/api/connections/{$connection->id}/tables/products/data?limit=2&offset=2"
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function itRequiresViewAuthorizationToGetData(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/connections/{$connection->id}/tables/some_table/data");

        $response->assertForbidden();
    }

    #[Test]
    public function itInsertsRecordSuccessfully(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'articles',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'content', 'type' => 'text'],
            ],
        ])->assertCreated();

        // Insert data
        $response = $this->postJson("/api/connections/{$connection->id}/tables/articles/data", [
            'data' => [
                'title' => 'Test Article',
                'content' => 'Article content here',
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Record inserted successfully');
    }

    #[Test]
    public function itValidatesDataOnInsertion(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/connections/{$connection->id}/tables/some_table/data", []);

        $response->assertUnprocessable();
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToInsertData(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/connections/{$connection->id}/tables/some_table/data", [
            'data' => ['col' => 'value'],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function itUpdatesRecordSuccessfully(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'records',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'status', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert record
        $this->postJson("/api/connections/{$connection->id}/tables/records/data", [
            'data' => ['status' => 'active'],
        ])->assertCreated();

        // Update record (using id as primary key)
        $response = $this->putJson("/api/connections/{$connection->id}/tables/records/data", [
            'pk' => ['id' => 1],
            'data' => ['status' => 'inactive'],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Record updated successfully');
    }

    #[Test]
    public function itValidatesUpdateRequest(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        // Missing pk
        $response = $this->putJson("/api/connections/{$connection->id}/tables/some_table/data", [
            'data' => ['col' => 'value'],
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToUpdateData(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/connections/{$connection->id}/tables/some_table/data", [
            'pk' => ['id' => 1],
            'data' => ['col' => 'value'],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function itDeletesRecordSuccessfully(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_data_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'deletable',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'value', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Insert record
        $this->postJson("/api/connections/{$connection->id}/tables/deletable/data", [
            'data' => ['value' => 'test'],
        ])->assertCreated();

        // Delete record
        $response = $this->deleteJson(
            "/api/connections/{$connection->id}/tables/deletable/data?id=1"
        );

        $response->assertOk()
            ->assertJsonPath('message', 'Record deleted successfully');
    }

    #[Test]
    public function itRequiresPrimaryKeyForDeletion(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/connections/{$connection->id}/tables/some_table/data"
        );

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Primary key columns required');
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToDeleteData(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson(
            "/api/connections/{$connection->id}/tables/some_table/data?id=1"
        );

        $response->assertForbidden();
    }

    #[Test]
    public function itReturns404ForNonExistentConnection(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/connections/non-existent-uuid/tables/some_table/data');

        $response->assertNotFound();
    }
}
