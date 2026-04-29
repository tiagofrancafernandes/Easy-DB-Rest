<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TableControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $dbPath = database_path('table_controller_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);
    }

    protected function tearDown(): void
    {
        $dbPath = database_path('table_controller_test.sqlite');

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        parent::tearDown();
    }

    #[Test]
    public function itListsTablesInConnection(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/connections/{$connection->id}/tables");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    #[Test]
    public function itRequiresViewAuthorizationToListTables(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/connections/{$connection->id}/tables");

        $response->assertForbidden();
    }

    #[Test]
    public function itShowsTableDetails(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // First create a table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'test_table',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'name', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Then show its details
        $response = $this->getJson("/api/connections/{$connection->id}/tables/test_table");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['columns']]);
    }

    #[Test]
    public function itRequiresViewAuthorizationToShowTable(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/connections/{$connection->id}/tables/some_table");

        $response->assertForbidden();
    }

    #[Test]
    public function itCreatesTableWithColumns(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'new_table',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'email', 'type' => 'string'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Table created successfully');
    }

    #[Test]
    public function itValidatesTableNameOnCreation(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/connections/{$connection->id}/tables", [
            'columns' => [['name' => 'id', 'type' => 'id']],
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function itValidatesColumnsOnTableCreation(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'new_table',
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function itValidatesColumnStructure(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'new_table',
            'columns' => [
                ['name' => 'id'],  // Missing type
            ],
        ]);

        $response->assertUnprocessable();
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToCreateTable(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'new_table',
            'columns' => [['name' => 'id', 'type' => 'id']],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function itUpdatesTableStructureWithAddColumn(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table first
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'update_test',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
            ],
        ])->assertCreated();

        // Add column
        $response = $this->putJson("/api/connections/{$connection->id}/tables/update_test", [
            'add' => [
                ['name' => 'new_column', 'type' => 'string'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Table altered successfully');
    }

    #[Test]
    public function itUpdatesTableStructureWithDropColumn(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'drop_test',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'remove_me', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Drop column
        $response = $this->putJson("/api/connections/{$connection->id}/tables/drop_test", [
            'drop' => ['remove_me'],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Table altered successfully');
    }

    #[Test]
    public function itUpdatesTableStructureWithModifyColumn(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'modify_test',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'status', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Modify column
        $response = $this->putJson("/api/connections/{$connection->id}/tables/modify_test", [
            'modify' => [
                ['name' => 'status', 'type' => 'text'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Table altered successfully');
    }

    #[Test]
    public function itUpdatesTableStructureWithRenameColumn(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'rename_test',
            'columns' => [
                ['name' => 'id', 'type' => 'id'],
                ['name' => 'old_name', 'type' => 'string'],
            ],
        ])->assertCreated();

        // Rename column
        $response = $this->putJson("/api/connections/{$connection->id}/tables/rename_test", [
            'rename' => ['old_name' => 'new_name'],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Table altered successfully');
    }

    #[Test]
    public function itValidatesAlterTableRequest(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($user);

        // Send invalid request
        $response = $this->putJson("/api/connections/{$connection->id}/tables/some_table", [
            'invalid_field' => 'value',
        ]);

        // Should still pass validation (all fields are optional)
        $response->assertOk();
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToAlterTable(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/connections/{$connection->id}/tables/some_table", [
            'add' => [['name' => 'col', 'type' => 'string']],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function itDropsATable(): void
    {
        $user = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $user->id,
            'driver' => 'sqlite',
            'database' => database_path('table_controller_test.sqlite'),
        ]);

        Sanctum::actingAs($user);

        // Create table
        $this->postJson("/api/connections/{$connection->id}/tables", [
            'table' => 'drop_me',
            'columns' => [['name' => 'id', 'type' => 'id']],
        ])->assertCreated();

        // Drop table
        $response = $this->deleteJson("/api/connections/{$connection->id}/tables/drop_me");

        $response->assertOk()
            ->assertJsonPath('message', 'Table dropped successfully');
    }

    #[Test]
    public function itRequiresUpdateAuthorizationToDropTable(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $connection = Connection::factory()->create([
            'user_id' => $owner->id,
            'driver' => 'sqlite',
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/connections/{$connection->id}/tables/some_table");

        $response->assertForbidden();
    }

    #[Test]
    public function itReturns404ForNonExistentConnection(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/connections/non-existent-uuid/tables');

        $response->assertNotFound();
    }
}
