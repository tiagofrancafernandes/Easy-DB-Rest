<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itCreatesATeamAndAddsOwnerAsMember(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/teams', [
            'name' => 'Dream Team',
        ]);

        $response->assertCreated();
        $team = Team::first();

        $this->assertEquals('Dream Team', $team->name);
        $this->assertEquals($user->id, $team->owner_id);
        $this->assertTrue($team->members->contains($user->id));
    }

    #[Test]
    public function itAddsAndRemovesMembers(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        Sanctum::actingAs($owner);

        // Add member
        $this->postJson("/api/teams/{$team->id}/members", ['user_id' => $member->id])
            ->assertOk();

        $this->assertTrue($team->fresh()->members->contains($member->id));

        // Remove member
        $this->deleteJson("/api/teams/{$team->id}/members/{$member->id}")
            ->assertOk();

        $this->assertFalse($team->fresh()->members->contains($member->id));
    }

    #[Test]
    public function onlyOwnerCanManageMembers(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);
        $team->members()->attach($member->id);

        Sanctum::actingAs($member);

        $this->postJson("/api/teams/{$team->id}/members", ['user_id' => $stranger->id])
            ->assertForbidden();
    }
}
