<?php

use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects task status routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/projects/1/task-statuses'],
    ['POST', '/api/projects/1/task-statuses'],
    ['GET', '/api/projects/1/task-statuses/1'],
    ['PATCH', '/api/projects/1/task-statuses/1'],
    ['DELETE', '/api/projects/1/task-statuses/1'],
]);

it('creates a task status for an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'task-status-create@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);

    taskStatusEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/task-statuses", [
        'name' => 'Ready for Review',
        'slug' => 'ready-for-review',
        'color' => '#44aa88',
        'position' => 2,
        'is_default' => true,
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Task status created successfully',
            'data' => [
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
                'name' => 'Ready for Review',
                'slug' => 'ready-for-review',
                'color' => '#44aa88',
                'position' => 2,
                'is_default' => true,
            ],
        ]);

    $this->assertDatabaseHas('task_statuses', [
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Ready for Review',
        'slug' => 'ready-for-review',
    ]);
});

it('returns validation errors when creating a task status with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'task-status-validation@example.com',
    ]);
    [, $project] = taskStatusEndpointProjectForUser($user);

    taskStatusEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/task-statuses", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'slug',
        ]);
});

it('rejects duplicate task status slugs in the same project scope', function () {
    $user = User::factory()->create([
        'email' => 'task-status-duplicate@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);

    TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'slug' => 'duplicate-status',
    ]);

    taskStatusEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/task-statuses", [
        'name' => 'Duplicate Status',
        'slug' => 'duplicate-status',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'slug',
        ]);
});

it('lists task statuses for an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'task-status-index@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);
    $visibleStatus = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Visible Status',
        'slug' => 'visible-status',
        'position' => 1,
    ]);

    TaskStatus::factory()->create([
        'name' => 'Hidden Status',
        'slug' => 'hidden-status',
    ]);

    taskStatusEndpointLoginAs($user);

    $this->getJson("/api/projects/{$project->id}/task-statuses")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task statuses retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $visibleStatus->id,
            'workspace_id' => $workspace->id,
            'project_id' => $project->id,
            'name' => 'Visible Status',
            'slug' => 'visible-status',
        ])
        ->assertJsonMissing([
            'slug' => 'hidden-status',
        ]);
});

it('shows an accessible task status', function () {
    $user = User::factory()->create([
        'email' => 'task-status-show@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);
    $status = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'name' => 'Visible Status',
        'slug' => 'visible-status',
    ]);

    taskStatusEndpointLoginAs($user);

    $this->getJson("/api/projects/{$project->id}/task-statuses/{$status->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task status retrieved successfully',
            'data' => [
                'id' => $status->id,
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
                'name' => 'Visible Status',
                'slug' => 'visible-status',
            ],
        ]);
});

it('forbids inaccessible project and task status access', function () {
    $user = User::factory()->create([
        'email' => 'task-status-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskStatusEndpointProjectForUser($user);
    $hiddenWorkspace = Workspace::factory()->create();
    $hiddenTeam = Team::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
    ]);
    $hiddenProject = Project::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
        'team_id' => $hiddenTeam->id,
    ]);
    $hiddenStatus = TaskStatus::factory()->create([
        'workspace_id' => $hiddenWorkspace->id,
        'project_id' => $hiddenProject->id,
    ]);

    taskStatusEndpointLoginAs($user);

    $this->getJson("/api/projects/{$hiddenProject->id}/task-statuses")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
            'errors' => [
                'task_status' => ['You do not have access to this task status.'],
            ],
        ]);

    $this->getJson("/api/projects/{$accessibleProject->id}/task-statuses/{$hiddenStatus->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);
});

it('updates an accessible task status', function () {
    $user = User::factory()->create([
        'email' => 'task-status-update@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);
    $status = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
        'slug' => 'before-update',
    ]);

    taskStatusEndpointLoginAs($user);

    $this->patchJson("/api/projects/{$project->id}/task-statuses/{$status->id}", [
        'name' => 'After Update',
        'slug' => 'after-update',
        'position' => 4,
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => [
                'id' => $status->id,
                'workspace_id' => $workspace->id,
                'project_id' => $project->id,
                'name' => 'After Update',
                'slug' => 'after-update',
                'position' => 4,
            ],
        ]);

    $this->assertDatabaseHas('task_statuses', [
        'id' => $status->id,
        'name' => 'After Update',
        'slug' => 'after-update',
        'position' => 4,
    ]);
});

it('forbids updating an inaccessible task status', function () {
    $user = User::factory()->create([
        'email' => 'task-status-update-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskStatusEndpointProjectForUser($user);
    $hiddenStatus = TaskStatus::factory()->create([
        'name' => 'Do Not Touch',
    ]);

    taskStatusEndpointLoginAs($user);

    $this->patchJson("/api/projects/{$accessibleProject->id}/task-statuses/{$hiddenStatus->id}", [
        'name' => 'Changed Anyway',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);

    $this->assertDatabaseHas('task_statuses', [
        'id' => $hiddenStatus->id,
        'name' => 'Do Not Touch',
    ]);
});

it('soft deletes an accessible task status', function () {
    $user = User::factory()->create([
        'email' => 'task-status-delete@example.com',
    ]);
    [$workspace, $project] = taskStatusEndpointProjectForUser($user);
    $status = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ]);

    taskStatusEndpointLoginAs($user);

    $this->deleteJson("/api/projects/{$project->id}/task-statuses/{$status->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task status deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('task_statuses', [
        'id' => $status->id,
    ]);
});

it('forbids deleting an inaccessible task status', function () {
    $user = User::factory()->create([
        'email' => 'task-status-delete-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskStatusEndpointProjectForUser($user);
    $hiddenStatus = TaskStatus::factory()->create();

    taskStatusEndpointLoginAs($user);

    $this->deleteJson("/api/projects/{$accessibleProject->id}/task-statuses/{$hiddenStatus->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);

    $this->assertNotSoftDeleted('task_statuses', [
        'id' => $hiddenStatus->id,
    ]);
});

function taskStatusEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Project}
 */
function taskStatusEndpointProjectForUser(User $user): array
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
