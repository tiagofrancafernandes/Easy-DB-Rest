<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Snippet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SnippetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itListsOwnedAndSharedSnippets(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $owned = Snippet::factory()->create(['user_id' => $user->id]);
        $other = Snippet::factory()->create(['user_id' => $otherUser->id]);

        $team = Team::factory()->create(['owner_id' => $otherUser->id]);
        $team->members()->attach($user->id);
        $other->teams()->attach($team->id, ['permission' => 'view']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/snippets');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $owned->id)
            ->assertJsonPath('data.1.id', $other->id);
    }

    #[Test]
    public function itCreatesASnippetAndInfersType(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/snippets', [
            'name' => 'test.php',
            'content' => '<?php echo "hello";',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'php');

        $this->assertDatabaseHas('snippets', [
            'name' => 'test.php',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function itEnforcesViewPolicy(): void
    {
        $user = User::factory()->create();
        $otherSnippet = Snippet::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/snippets/{$otherSnippet->id}");

        $response->assertForbidden();
    }

    #[Test]
    public function itAllowsPublicAccessWithSlugAndPassword(): void
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create([
            'user_id' => $user->id,
            'public_content_slug' => 'my-secret-sql',
            'public_content_password' => 'secret123',
            'content' => 'SELECT 1;',
        ]);

        // Unauthorized access
        $response = $this->getJson("/api/snippets/{$user->id}/my-secret-sql");
        $response->assertStatus(401);

        // Access with password in query
        $response = $this->getJson("/api/snippets/{$user->id}/my-secret-sql?password=secret123");
        $response->assertOk()
            ->assertJsonPath('data.content', 'SELECT 1;');

        // Access as plain text
        $response = $this->getJson("/api/snippets/{$user->id}/my-secret-sql?password=secret123&text");
        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('SELECT 1;');

        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    #[Test]
    public function itSharesSnippetWithTeam(): void
    {
        $user = User::factory()->create();
        $snippet = Snippet::factory()->create(['user_id' => $user->id]);
        $team = Team::factory()->create(['owner_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/snippets/{$snippet->id}/share", [
            'team_id' => $team->id,
            'permission' => 'edit',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('snippet_team', [
            'snippet_id' => $snippet->id,
            'team_id' => $team->id,
            'permission' => 'edit',
        ]);
    }
}
