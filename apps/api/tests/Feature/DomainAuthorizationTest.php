<?php

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects unauthenticated requests to representative domain routes', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces'],
    ['GET', '/api/workspaces/1/teams'],
    ['GET', '/api/workspaces/1/teams/1/members'],
    ['GET', '/api/workspaces/1/projects'],
    ['GET', '/api/workspaces/1/projects/1/members'],
    ['GET', '/api/projects/1/task-statuses'],
    ['GET', '/api/projects/1/tasks'],
    ['GET', '/api/tasks/1/comments'],
]);

it('rejects disabled users from representative domain routes', function (string $route) {
    $user = User::factory()->create([
        'email' => 'domain-disabled@example.com',
    ]);
    [$workspace, $team, $project, , $task] = domainAuthorizationGraphForUser($user);

    domainAuthorizationLoginAs($user);

    $user->forceFill([
        'disabled_at' => now(),
    ])->save();

    $uri = match ($route) {
        'workspace' => "/api/workspaces/{$workspace->id}",
        'team' => "/api/workspaces/{$workspace->id}/teams",
        'team_member' => "/api/workspaces/{$workspace->id}/teams/{$team->id}/members",
        'project' => "/api/workspaces/{$workspace->id}/projects",
        'project_member' => "/api/workspaces/{$workspace->id}/projects/{$project->id}/members",
        'task_status' => "/api/projects/{$project->id}/task-statuses",
        'task' => "/api/projects/{$project->id}/tasks",
        'comment' => "/api/tasks/{$task->id}/comments",
    };

    $this->getJson($uri)
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Account is disabled',
            'errors' => [
                'account' => ['This account has been disabled.'],
            ],
        ]);
})->with([
    'workspace',
    'team',
    'team_member',
    'project',
    'project_member',
    'task_status',
    'task',
    'comment',
]);

it('rejects access to resources in inaccessible workspaces projects and tasks', function () {
    $user = User::factory()->create([
        'email' => 'domain-inaccessible@example.com',
    ]);
    [$workspace, , $project, , $task] = domainAuthorizationGraphForUser($user);
    [$hiddenWorkspace, $hiddenTeam, $hiddenProject, $hiddenStatus, $hiddenTask] = domainAuthorizationGraph();
    $hiddenComment = Comment::factory()->create([
        'task_id' => $hiddenTask->id,
    ]);

    domainAuthorizationLoginAs($user);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Workspace access denied',
        ]);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/teams")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->getJson("/api/workspaces/{$workspace->id}/teams/{$hiddenTeam->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->getJson("/api/workspaces/{$workspace->id}/teams/{$hiddenTeam->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->getJson("/api/workspaces/{$hiddenWorkspace->id}/projects")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->getJson("/api/workspaces/{$workspace->id}/projects/{$hiddenProject->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->getJson("/api/workspaces/{$workspace->id}/projects/{$hiddenProject->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->getJson("/api/projects/{$hiddenProject->id}/task-statuses")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);

    $this->getJson("/api/projects/{$project->id}/task-statuses/{$hiddenStatus->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);

    $this->getJson("/api/projects/{$hiddenProject->id}/tasks")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);

    $this->getJson("/api/projects/{$project->id}/tasks/{$hiddenTask->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);

    $this->getJson("/api/tasks/{$hiddenTask->id}/comments")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment access denied',
        ]);

    $this->getJson("/api/tasks/{$task->id}/comments/{$hiddenComment->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment access denied',
        ]);
});

it('rejects nested route mismatches for otherwise accessible resources', function () {
    $user = User::factory()->create([
        'email' => 'domain-mismatch@example.com',
    ]);
    [$firstWorkspace, , $firstProject, , $firstTask] = domainAuthorizationGraphForUser($user);
    [, $secondTeam, $secondProject, $secondStatus, $secondTask] = domainAuthorizationGraphForUser($user);
    $secondComment = Comment::factory()->create([
        'task_id' => $secondTask->id,
    ]);

    domainAuthorizationLoginAs($user);

    $this->getJson("/api/workspaces/{$firstWorkspace->id}/teams/{$secondTeam->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->getJson("/api/workspaces/{$firstWorkspace->id}/teams/{$secondTeam->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Team access denied',
        ]);

    $this->getJson("/api/workspaces/{$firstWorkspace->id}/projects/{$secondProject->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->getJson("/api/workspaces/{$firstWorkspace->id}/projects/{$secondProject->id}/members")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Project access denied',
        ]);

    $this->getJson("/api/projects/{$firstProject->id}/task-statuses/{$secondStatus->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task status access denied',
        ]);

    $this->getJson("/api/projects/{$firstProject->id}/tasks/{$secondTask->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Task access denied',
        ]);

    $this->getJson("/api/tasks/{$firstTask->id}/comments/{$secondComment->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment access denied',
        ]);
});

it('rejects non-authors from updating or deleting comments', function () {
    $author = User::factory()->create([
        'email' => 'domain-comment-author@example.com',
    ]);
    $otherUser = User::factory()->create([
        'email' => 'domain-comment-other@example.com',
    ]);
    [$workspace, , , , $task] = domainAuthorizationGraphForUser($author);
    $otherTeam = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);
    $otherTeam->users()->attach($otherUser->id);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'body' => 'Author-owned comment.',
    ]);

    domainAuthorizationLoginAs($otherUser);

    $this->patchJson("/api/tasks/{$task->id}/comments/{$comment->id}", [
        'body' => 'Updated by another user.',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment author required',
            'errors' => [
                'comment' => ['Only the comment author can modify this comment.'],
            ],
        ]);

    $this->deleteJson("/api/tasks/{$task->id}/comments/{$comment->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment author required',
        ]);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'Author-owned comment.',
    ]);
    $this->assertNotSoftDeleted('comments', [
        'id' => $comment->id,
    ]);
});

function domainAuthorizationLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Team, Project, TaskStatus, Task}
 */
function domainAuthorizationGraphForUser(User $user): array
{
    [$workspace, $team, $project, $status, $task] = domainAuthorizationGraph();

    $team->users()->attach($user->id);

    return [$workspace, $team, $project, $status, $task];
}

/**
 * @return array{Workspace, Team, Project, TaskStatus, Task}
 */
function domainAuthorizationGraph(): array
{
    $workspace = Workspace::factory()->create();
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);
    $project = Project::factory()->create([
        'workspace_id' => $workspace->id,
        'team_id' => $team->id,
    ]);
    $status = TaskStatus::factory()->create([
        'workspace_id' => $workspace->id,
        'project_id' => $project->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
    ]);

    return [$workspace, $team, $project, $status, $task];
}
