<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConnectionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function sqlitePayload(string $name = 'Test DB'): array
    {
        return [
            'name'     => $name,
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
        ];
    }

    #[Test]
    public function itListsAllConnections(): void
    {
        Connection::factory()->count(3)->create();

        $response = $this->getJson('/api/connections');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function itCreatesAConnection(): void
    {
        $response = $this->postJson('/api/connections', $this->sqlitePayload());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test DB')
            ->assertJsonPath('data.driver', 'sqlite')
            ->assertJsonMissing(['password']);

        $this->assertDatabaseHas('connections', ['name' => 'Test DB']);
    }

    #[Test]
    public function itRejectsAnInvalidDriver(): void
    {
        $response = $this->postJson('/api/connections', [
            'name'     => 'Bad',
            'driver'   => 'oracle',
            'database' => 'mydb',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    #[Test]
    public function itRequiresNameAndDatabase(): void
    {
        $response = $this->postJson('/api/connections', [
            'driver' => 'sqlite',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    #[Test]
    public function itShowsASingleConnection(): void
    {
        $connection = Connection::factory()->create(['name' => 'My Conn']);

        $response = $this->getJson("/api/connections/{$connection->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $connection->id)
            ->assertJsonPath('data.name', 'My Conn');
    }

    #[Test]
    public function itReturns404ForNonExistentConnection(): void
    {
        $response = $this->getJson('/api/connections/non-existent-uuid');

        $response->assertNotFound();
    }

    #[Test]
    public function itUpdatesAConnection(): void
    {
        $connection = Connection::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/connections/{$connection->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('connections', ['name' => 'New Name']);
    }

    #[Test]
    public function itDeletesAConnection(): void
    {
        $connection = Connection::factory()->create();

        $response = $this->deleteJson("/api/connections/{$connection->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('connections', ['id' => $connection->id]);
    }

    #[Test]
    public function itNeverExposesPasswordInResponse(): void
    {
        $payload           = $this->sqlitePayload();
        $payload['password'] = 'super-secret';

        $response = $this->postJson('/api/connections', $payload);

        $response->assertCreated();

        $body = $response->json();

        $this->assertArrayNotHasKey('password', $body['data']);
    }

    #[Test]
    public function itCreatesAConnectionWithTags(): void
    {
        $payload = $this->sqlitePayload('Tagged DB');
        $payload['tags'] = ['production', 'sqlite'];

        $response = $this->postJson('/api/connections', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.tags.0', 'production')
            ->assertJsonPath('data.tags.1', 'sqlite');
    }

    #[Test]
    public function itCreatesAConnectionViaUrlWithoutDatabaseField(): void
    {
        $payload = [
            'name'   => 'URL DB',
            'driver' => 'pgsql',
            'url'    => 'postgresql://user:pass@host:5432/dbname',
        ];

        $response = $this->postJson('/api/connections', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.url', $payload['url'])
            ->assertJsonPath('data.database', null);
    }
}
