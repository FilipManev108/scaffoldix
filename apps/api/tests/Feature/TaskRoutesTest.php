<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects task routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/projects/1/tasks'],
    ['POST', '/api/projects/1/tasks'],
    ['GET', '/api/projects/1/tasks/1'],
    ['PATCH', '/api/projects/1/tasks/1'],
    ['DELETE', '/api/projects/1/tasks/1'],
]);

it('creates a task for an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'task-create@example.com',
    ]);
    $assignee = User::factory()->create([
        'email' => 'task-assignee@example.com',
    ]);
    [, $project, $status] = taskEndpointProjectForUser($user);

    taskEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/tasks", [
        'task_status_id' => $status->id,
        'assigned_to' => $assignee->id,
        'title' => 'Build task API',
        'description' => 'Implement the core task endpoints.',
        'priority' => 'high',
        'due_date' => '2026-06-15',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'project_id' => $project->id,
                'task_status_id' => $status->id,
                'created_by' => $user->id,
                'assigned_to' => $assignee->id,
                'title' => 'Build task API',
                'priority' => 'high',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
        'assigned_to' => $assignee->id,
        'title' => 'Build task API',
    ]);
});

it('returns validation errors when creating a task with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'task-validation@example.com',
    ]);
    [, $project] = taskEndpointProjectForUser($user);

    taskEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/tasks", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'task_status_id',
            'title',
        ]);

    $this->postJson("/api/projects/{$project->id}/tasks", [
        'task_status_id' => 999999,
        'title' => 'Invalid status task',
        'assigned_to' => 999999,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'task_status_id',
            'assigned_to',
        ]);
});

it('rejects task statuses from another project', function () {
    $user = User::factory()->create([
        'email' => 'task-invalid-status@example.com',
    ]);
    [, $project] = taskEndpointProjectForUser($user);
    [, $otherProject, $otherStatus] = taskEndpointProjectForUser(User::factory()->create());

    taskEndpointLoginAs($user);

    $this->postJson("/api/projects/{$project->id}/tasks", [
        'task_status_id' => $otherStatus->id,
        'title' => 'Wrong project status',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'task_status_id',
        ]);

    expect($otherProject->id)->not->toBe($project->id);
});

it('lists tasks for an accessible project', function () {
    $user = User::factory()->create([
        'email' => 'task-index@example.com',
    ]);
    [, $project, $status] = taskEndpointProjectForUser($user);
    $visibleTask = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
        'title' => 'Visible Task',
    ]);

    Task::factory()->create([
        'title' => 'Hidden Task',
    ]);

    taskEndpointLoginAs($user);

    $this->getJson("/api/projects/{$project->id}/tasks")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $visibleTask->id,
            'project_id' => $project->id,
            'task_status_id' => $status->id,
            'created_by' => $user->id,
            'title' => 'Visible Task',
        ])
        ->assertJsonMissing([
            'title' => 'Hidden Task',
        ]);
});

it('shows an accessible task', function () {
    $user = User::factory()->create([
        'email' => 'task-show@example.com',
    ]);
    [, $project, $status] = taskEndpointProjectForUser($user);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
        'title' => 'Visible Task',
    ]);

    taskEndpointLoginAs($user);

    $this->getJson("/api/projects/{$project->id}/tasks/{$task->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task retrieved successfully',
            'data' => [
                'id' => $task->id,
                'project_id' => $project->id,
                'task_status_id' => $status->id,
                'created_by' => $user->id,
                'title' => 'Visible Task',
            ],
        ]);
});

it('forbids inaccessible project and task access', function () {
    $user = User::factory()->create([
        'email' => 'task-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskEndpointProjectForUser($user);
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
    $hiddenTask = Task::factory()->create([
        'project_id' => $hiddenProject->id,
        'task_status_id' => $hiddenStatus->id,
    ]);

    taskEndpointLoginAs($user);

    $this->getJson("/api/projects/{$hiddenProject->id}/tasks")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
            'errors' => [
                'task' => ['You do not have access to this task.'],
            ],
        ]);

    $this->getJson("/api/projects/{$accessibleProject->id}/tasks/{$hiddenTask->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);
});

it('updates an accessible task', function () {
    $user = User::factory()->create([
        'email' => 'task-update@example.com',
    ]);
    $assignee = User::factory()->create([
        'email' => 'task-update-assignee@example.com',
    ]);
    [, $project, $status] = taskEndpointProjectForUser($user);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
        'title' => 'Before Update',
    ]);

    taskEndpointLoginAs($user);

    $this->patchJson("/api/projects/{$project->id}/tasks/{$task->id}", [
        'title' => 'After Update',
        'assigned_to' => $assignee->id,
        'priority' => 'urgent',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'id' => $task->id,
                'project_id' => $project->id,
                'task_status_id' => $status->id,
                'title' => 'After Update',
                'assigned_to' => $assignee->id,
                'priority' => 'urgent',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'After Update',
        'assigned_to' => $assignee->id,
        'priority' => 'urgent',
    ]);
});

it('forbids updating an inaccessible task', function () {
    $user = User::factory()->create([
        'email' => 'task-update-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskEndpointProjectForUser($user);
    $hiddenTask = Task::factory()->create([
        'title' => 'Do Not Touch',
    ]);

    taskEndpointLoginAs($user);

    $this->patchJson("/api/projects/{$accessibleProject->id}/tasks/{$hiddenTask->id}", [
        'title' => 'Changed Anyway',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);

    $this->assertDatabaseHas('tasks', [
        'id' => $hiddenTask->id,
        'title' => 'Do Not Touch',
    ]);
});

it('soft deletes an accessible task', function () {
    $user = User::factory()->create([
        'email' => 'task-delete@example.com',
    ]);
    [, $project, $status] = taskEndpointProjectForUser($user);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
    ]);

    taskEndpointLoginAs($user);

    $this->deleteJson("/api/projects/{$project->id}/tasks/{$task->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Task deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('tasks', [
        'id' => $task->id,
    ]);
});

it('forbids deleting an inaccessible task', function () {
    $user = User::factory()->create([
        'email' => 'task-delete-forbidden@example.com',
    ]);
    [, $accessibleProject] = taskEndpointProjectForUser($user);
    $hiddenTask = Task::factory()->create();

    taskEndpointLoginAs($user);

    $this->deleteJson("/api/projects/{$accessibleProject->id}/tasks/{$hiddenTask->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);

    $this->assertNotSoftDeleted('tasks', [
        'id' => $hiddenTask->id,
    ]);
});

function taskEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Project, TaskStatus}
 */
function taskEndpointProjectForUser(User $user): array
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
    $status = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ]);

    $team->users()->attach($user->id);

    return [$workspace, $project, $status];
}
