<?php

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects project routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces/1/projects'],
    ['POST', '/api/workspaces/1/projects'],
    ['GET', '/api/workspaces/1/projects/1'],
    ['PATCH', '/api/workspaces/1/projects/1'],
    ['DELETE', '/api/workspaces/1/projects/1'],
]);

it('creates a project in an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'project-create@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);

    projectEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/projects", [
        'team_id' => $team->id,
        'name' => 'Billing Portal',
        'slug' => 'billing-portal',
        'description' => 'Customer billing work.',
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-07-01',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => [
                'workspace_id' => $workspace->id,
                'team_id' => $team->id,
                'created_by' => $user->id,
                'name' => 'Billing Portal',
                'slug' => 'billing-portal',
            ],
        ])
        ->assertJsonMissingPath('data.description');

    $this->assertDatabaseHas('projects', [
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
        'name' => 'Billing Portal',
        'slug' => 'billing-portal',
    ]);
});

it('returns validation errors when creating a project with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'project-validation@example.com',
    ]);
    [$workspace] = projectEndpointWorkspaceForUser($user);

    projectEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/projects", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'team_id',
            'name',
            'slug',
        ]);
});

it('rejects duplicate project slugs within the same workspace', function () {
    $user = User::factory()->create([
        'email' => 'project-duplicate@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);

    Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'slug' => 'duplicate-project',
    ]);

    projectEndpointLoginAs($user);

    $this->postJson("/api/workspaces/{$workspace->id}/projects", [
        'team_id' => $team->id,
        'name' => 'Duplicate Project',
        'slug' => 'duplicate-project',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'slug',
        ]);
});

it('lists only projects in an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'project-index@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);
    $visibleProject = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
        'name' => 'Visible Project',
        'slug' => 'visible-project',
    ]);

    Project::factory()->create([
        'name' => 'Hidden Project',
        'slug' => 'hidden-project',
    ]);

    projectEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}/projects")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Projects retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $visibleProject->id,
            'workspace_id' => $workspace->id,
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'Visible Project',
            'slug' => 'visible-project',
        ])
        ->assertJsonMissing([
            'slug' => 'hidden-project',
        ]);
});

it('shows an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'project-show@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
        'name' => 'Visible Project',
        'slug' => 'visible-project',
    ]);

    projectEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}/projects/{$project->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Project retrieved successfully',
            'data' => [
                'id' => $project->id,
                'workspace_id' => $workspace->id,
                'team_id' => $team->id,
                'created_by' => $user->id,
                'name' => 'Visible Project',
                'slug' => 'visible-project',
            ],
        ])
        ->assertJsonMissingPath('data.description');
});

it('forbids inaccessible project and workspace access', function () {
    $user = User::factory()->create([
        'email' => 'project-show-forbidden@example.com',
    ]);
    [$accessibleWorkspace] = projectEndpointWorkspaceForUser($user);
    $hiddenWorkspace = Workspace::factory()->create();
    $hiddenTeam = Team::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
    ]);
    $hiddenProject = Project::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
        'team_id' => $hiddenTeam->id,
    ]);

    projectEndpointLoginAs($user);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/projects")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
            'errors' => [
                'project' => ['You do not have access to this project.'],
            ],
        ]);

    $this->getJson("/api/workspaces/{$accessibleWorkspace->id}/projects/{$hiddenProject->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);
});

it('updates an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'project-update@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
        'slug' => 'before-update',
    ]);

    projectEndpointLoginAs($user);

    $this->patchJson("/api/workspaces/{$workspace->id}/projects/{$project->id}", [
        'name' => 'After Update',
        'slug' => 'after-update',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => [
                'id' => $project->id,
                'workspace_id' => $workspace->id,
                'team_id' => $team->id,
                'name' => 'After Update',
                'slug' => 'after-update',
            ],
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'After Update',
        'slug' => 'after-update',
    ]);
});

it('forbids updating an inaccessible project', function () {
    $user = User::factory()->create([
        'email' => 'project-update-forbidden@example.com',
    ]);
    [$accessibleWorkspace] = projectEndpointWorkspaceForUser($user);
    $hiddenProject = Project::factory()->create([
        'name' => 'Do Not Touch',
    ]);

    projectEndpointLoginAs($user);

    $this->patchJson("/api/workspaces/{$accessibleWorkspace->id}/projects/{$hiddenProject->id}", [
        'name' => 'Changed Anyway',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->assertDatabaseHas('projects', [
        'id' => $hiddenProject->id,
        'name' => 'Do Not Touch',
    ]);
});

it('soft deletes an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'project-delete@example.com',
    ]);
    [$workspace, $team] = projectEndpointWorkspaceForUser($user);
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
        'created_by' => $user->id,
    ]);

    projectEndpointLoginAs($user);

    $this->deleteJson("/api/workspaces/{$workspace->id}/projects/{$project->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Project deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('projects', [
        'id' => $project->id,
    ]);
});

it('forbids deleting an inaccessible project', function () {
    $user = User::factory()->create([
        'email' => 'project-delete-forbidden@example.com',
    ]);
    [$accessibleWorkspace] = projectEndpointWorkspaceForUser($user);
    $hiddenProject = Project::factory()->create();

    projectEndpointLoginAs($user);

    $this->deleteJson("/api/workspaces/{$accessibleWorkspace->id}/projects/{$hiddenProject->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->assertNotSoftDeleted('projects', [
        'id' => $hiddenProject->id,
    ]);
});

function projectEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Team}
 */
function projectEndpointWorkspaceForUser(User $user): array
{
    $workspace = Workspace::factory()->create();
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $team->users()->attach($user->id);

    return [$workspace, $team];
}
