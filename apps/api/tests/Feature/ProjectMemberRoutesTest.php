<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects project member routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces/1/projects/1/members'],
    ['POST', '/api/workspaces/1/projects/1/members'],
    ['DELETE', '/api/workspaces/1/projects/1/members/1'],
]);

it('lists project members for an accessible project', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-list-manager@example.com',
        'name' => 'Manager User',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-list-member@example.com',
        'name' => 'Member User',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    $project->users()->attach($member->id);

    projectMemberEndpointLoginAs($manager);

    $this->getJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Project members retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $member->id,
            'name' => 'Member User',
            'email' => 'project-member-list-member@example.com',
        ]);
});

it('adds a member to an accessible project', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-add-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-add-member@example.com',
        'name' => 'Added Member',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    projectMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Project member added successfully',
            'data' => [
                'id' => $member->id,
                'name' => 'Added Member',
                'email' => 'project-member-add-member@example.com',
            ],
        ]);

    $this->assertDatabaseHas('project_user', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);
});

it('returns validation errors when adding an invalid project member', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-validation@example.com',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    projectMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);

    $this->postJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members", [
        'user_id' => 999999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);
});

it('rejects duplicate project membership', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-duplicate-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-duplicate-member@example.com',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    $project->users()->attach($member->id);

    projectMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'user_id',
        ]);
});

it('removes a member from an accessible project', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-remove-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-remove-member@example.com',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    $project->users()->attach($member->id);

    projectMemberEndpointLoginAs($manager);

    $this->deleteJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members/{$member->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Project member removed successfully',
            'data' => null,
        ]);

    $this->assertDatabaseMissing('project_user', [
        'project_id' => $project->id,
        'user_id' => $member->id,
    ]);
});

it('forbids inaccessible project membership management', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-forbidden-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-forbidden-member@example.com',
    ]);
    [$accessibleWorkspace] = projectMemberEndpointProjectForUser($manager);
    $hiddenWorkspace = Workspace::factory()->create();
    $hiddenTeam = Team::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
    ]);
    $hiddenProject = Project::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
        'team_id' => $hiddenTeam->id,
    ]);

    projectMemberEndpointLoginAs($manager);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/projects/{$hiddenProject->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
            'errors' => [
                'project' => ['You do not have access to this project.'],
            ],
        ]);

    $this->postJson("/api/workspaces/{$accessibleWorkspace->id}/projects/{$hiddenProject->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->deleteJson("/api/workspaces/{$accessibleWorkspace->id}/projects/{$hiddenProject->id}/members/{$member->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);
});

it('does not expose sensitive user fields in project member responses', function () {
    $manager = User::factory()->create([
        'email' => 'project-member-safe-manager@example.com',
    ]);
    $member = User::factory()->create([
        'email' => 'project-member-safe-member@example.com',
    ]);
    [$workspace, $project] = projectMemberEndpointProjectForUser($manager);

    projectMemberEndpointLoginAs($manager);

    $this->postJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members", [
        'user_id' => $member->id,
    ])
        ->assertCreated()
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonMissingPath('data.email_verified_at')
        ->assertJsonMissingPath('data.disabled_at')
        ->assertJsonMissingPath('data.roles')
        ->assertJsonMissingPath('data.permissions');

    $this->getJson("/api/workspaces/{$workspace->id}/projects/{$project->id}/members")
        ->assertOk()
        ->assertJsonMissingPath('data.0.password')
        ->assertJsonMissingPath('data.0.remember_token')
        ->assertJsonMissingPath('data.0.email_verified_at')
        ->assertJsonMissingPath('data.0.disabled_at')
        ->assertJsonMissingPath('data.0.roles')
        ->assertJsonMissingPath('data.0.permissions');
});

function projectMemberEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Project}
 */
function projectMemberEndpointProjectForUser(User $user): array
{
    $workspace = Workspace::factory()->create();
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    $team->users()->attach($user->id);

    return [$workspace, $project];
}
