<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects team routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces/1/teams'],
    ['POST', '/api/workspaces/1/teams'],
    ['GET', '/api/workspaces/1/teams/1'],
    ['PATCH', '/api/workspaces/1/teams/1'],
    ['DELETE', '/api/workspaces/1/teams/1'],
]);

it('creates a team in an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'team-create@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);

    teamEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/teams", [
        'name' => 'Platform Team',
        'slug' => 'platform-team',
        'description' => 'Owns the shared platform.',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => [
                'workspace_id' => $workspace->id,
                'name' => 'Platform Team',
                'slug' => 'platform-team',
            ],
        ])
        ->assertJsonMissingPath('data.description');

    $this->assertDatabaseHas('teams', [
        'workspace_id' => $workspace->id,
        'name' => 'Platform Team',
        'slug' => 'platform-team',
    ]);
});

it('returns validation errors when creating a team with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'team-validation@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);

    teamEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/teams", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'slug',
        ]);
});

it('rejects duplicate team slugs within the same workspace', function () {
    $user = User::factory()->create([
        'email' => 'team-duplicate@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);

    Team::factory()->create([
        'workspace_id' => $workspace->id,
        'slug' => 'duplicate-team',
    ]);

    teamEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/teams", [
        'name' => 'Duplicate Team',
        'slug' => 'duplicate-team',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'slug',
        ]);
});

it('lists teams only for an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'team-index@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);
    $visibleTeam = Team::factory()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Visible Team',
        'slug' => 'visible-team',
    ]);

    Team::factory()->create([
        'workspace_id' => Workspace::factory()->create()->id,
        'name' => 'Hidden Team',
        'slug' => 'hidden-team',
    ]);

    teamEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}/teams")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Teams retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $visibleTeam->id,
            'workspace_id' => $workspace->id,
            'name' => 'Visible Team',
            'slug' => 'visible-team',
        ])
        ->assertJsonMissing([
            'slug' => 'hidden-team',
        ]);
});

it('shows an accessible team', function () {
    $user = User::factory()->create([
        'email' => 'team-show@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
        'name' => 'Visible Team',
        'slug' => 'visible-team',
    ]);

    teamEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}/teams/{$team->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team retrieved successfully',
            'data' => [
                'id' => $team->id,
                'workspace_id' => $workspace->id,
                'name' => 'Visible Team',
                'slug' => 'visible-team',
            ],
        ])
        ->assertJsonMissingPath('data.description');
});

it('forbids inaccessible workspace and team access', function () {
    $user = User::factory()->create([
        'email' => 'team-show-forbidden@example.com',
    ]);

    $accessibleWorkspace = teamEndpointWorkspaceForUser($user);
    $hiddenWorkspace = Workspace::factory()->create();
    $hiddenTeam = Team::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
    ]);

    teamEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/teams")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
            'errors' => [
                'team' => ['You do not have access to this team.'],
            ],
        ]);

    $this->getJson("/api/workspaces/{$accessibleWorkspace->id}/teams/{$hiddenTeam->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);
});

it('updates an accessible team', function () {
    $user = User::factory()->create([
        'email' => 'team-update@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
        'slug' => 'before-update',
    ]);

    teamEndpointLoginAs($user);

    $this->patchJson("/api/workspaces/{$workspace->id}/teams/{$team->id}", [
        'name' => 'After Update',
        'slug' => 'after-update',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team updated successfully',
            'data' => [
                'id' => $team->id,
                'workspace_id' => $workspace->id,
                'name' => 'After Update',
                'slug' => 'after-update',
            ],
        ]);

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'After Update',
        'slug' => 'after-update',
    ]);
});

it('forbids updating an inaccessible team', function () {
    $user = User::factory()->create([
        'email' => 'team-update-forbidden@example.com',
    ]);

    $accessibleWorkspace = teamEndpointWorkspaceForUser($user);
    $hiddenTeam = Team::factory()->create([
        'name' => 'Do Not Touch',
    ]);

    teamEndpointLoginAs($user);

    $this->patchJson("/api/workspaces/{$accessibleWorkspace->id}/teams/{$hiddenTeam->id}", [
        'name' => 'Changed Anyway',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->assertDatabaseHas('teams', [
        'id' => $hiddenTeam->id,
        'name' => 'Do Not Touch',
    ]);
});

it('soft deletes an accessible team', function () {
    $user = User::factory()->create([
        'email' => 'team-delete@example.com',
    ]);

    $workspace = teamEndpointWorkspaceForUser($user);
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    teamEndpointLoginAs($user);

    $this->deleteJson("/api/workspaces/{$workspace->id}/teams/{$team->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Team deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('teams', [
        'id' => $team->id,
    ]);
});

it('forbids deleting an inaccessible team', function () {
    $user = User::factory()->create([
        'email' => 'team-delete-forbidden@example.com',
    ]);

    $accessibleWorkspace = teamEndpointWorkspaceForUser($user);
    $hiddenTeam = Team::factory()->create();

    teamEndpointLoginAs($user);

    $this->deleteJson("/api/workspaces/{$accessibleWorkspace->id}/teams/{$hiddenTeam->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->assertNotSoftDeleted('teams', [
        'id' => $hiddenTeam->id,
    ]);
});

function teamEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @param array<string, mixed> $attributes
 */
function teamEndpointWorkspaceForUser(User $user, array $attributes = []): Workspace
{
    $workspace = Workspace::factory()->create($attributes);
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $team->users()->attach($user->id);

    return $workspace;
}
