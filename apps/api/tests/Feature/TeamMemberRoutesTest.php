<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects team member routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces/1/teams/1/members'],
    ['POST', '/api/workspaces/1/teams/1/members'],
    ['DELETE', '/api/workspaces/1/teams/1/members/1'],
]);

it('lists team members for an accessible team', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-list-manager@example.com',
        'name' => 'Manager User',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-list-member@example.com',
        'name' => 'Member User',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    $team->users()->attach($member->id);

    teamMemberEndpointLoginAs($manager);

    $this->getJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team members retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $manager->id,
            'name' => 'Manager User',
            'email' => 'team-member-list-manager@example.com',
        ])
        ->assertJsonFragment([
            'id' => $member->id,
            'name' => 'Member User',
            'email' => 'team-member-list-member@example.com',
        ]);
});

it('adds a member to an accessible team', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-add-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-add-member@example.com',
        'name' => 'Added Member',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    teamMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Team member added successfully',
            'data' => [
                'id' => $member->id,
                'name' => 'Added Member',
                'email' => 'team-member-add-member@example.com',
            ],
        ]);

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});

it('returns validation errors when adding an invalid team member', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-validation@example.com',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    teamMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);

    $this->postJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members", [
        'user_id' => 999999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);
});

it('rejects duplicate team membership', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-duplicate-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-duplicate-member@example.com',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    $team->users()->attach($member->id);

    teamMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);
});

it('removes a member from an accessible team', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-remove-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-remove-member@example.com',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    $team->users()->attach($member->id);

    teamMemberEndpointLoginAs($manager);

    $this->deleteJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members/{$member->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team member removed successfully',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});

it('forbids inaccessible team membership management', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-forbidden-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-forbidden-member@example.com',
    ]);
    [$accessibleWorkspace] = teamMemberEndpointTeamForUser($manager);
    $hiddenWorkspace = Workspace::factory()->create();
    $hiddenTeam = Team::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
    ]);

    teamMemberEndpointLoginAs($manager);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/teams/{$hiddenTeam->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
            'errors' => [
                'team' => ['You do not have access to this team.'],
            ],
        ]);

    $this->postJson("/api/workspaces/{$accessibleWorkspace->id}/teams/{$hiddenTeam->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->deleteJson("/api/workspaces/{$accessibleWorkspace->id}/teams/{$hiddenTeam->id}/members/{$member->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);
});

it('does not expose sensitive user fields in member responses', function () {
    $manager = User::factory()->create([
        'email' => 'team-member-safe-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'team-member-safe-member@example.com',
    ]);
    [$workspace, $team] = teamMemberEndpointTeamForUser($manager);

    teamMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertCreated()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonMissingPath('data.email_verified_at')
        ->assertJsonMissingPath('data.disabled_at')
        ->assertJsonMissingPath('data.roles')
        ->assertJsonMissingPath('data.permissions');

    $this->getJson("/api/workspaces/{$workspace->id}/teams/{$team->id}/members")
        ->assertOk()
        ->assertJsonMissingPath('data.0.password')
        ->assertJsonMissingPath('data.0.remember_token')
        ->assertJsonMissingPath('data.0.email_verified_at')
        ->assertJsonMissingPath('data.0.disabled_at')
        ->assertJsonMissingPath('data.0.roles')
        ->assertJsonMissingPath('data.0.permissions');
});

function teamMemberEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Team}
 */
function teamMemberEndpointTeamForUser(User $user): array
{
    $workspace = Workspace::factory()->create();
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $team->users()->attach($user->id);

    return [$workspace, $team];
}
