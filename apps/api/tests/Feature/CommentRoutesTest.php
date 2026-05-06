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

it('protects comment routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/tasks/1/comments'],
    ['POST', '/api/tasks/1/comments'],
    ['GET', '/api/tasks/1/comments/1'],
    ['PATCH', '/api/tasks/1/comments/1'],
    ['DELETE', '/api/tasks/1/comments/1'],
]);

it('creates a comment for an accessible task', function () {
    $user = User::factory()->create([
        'email' => 'comment-create@example.com',
        'name' => 'Comment Author',
    ]);
    [, , $task] = commentEndpointTaskForUser($user);

    commentEndpointLoginAs($user);

    $this->postJson("/api/tasks/{$task->id}/comments", [
        'body' => 'This task is ready for review.',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'body' => 'This task is ready for review.',
                'author' => [
                    'id' => $user->id,
                    'name' => 'Comment Author',
                    'email' => 'comment-create@example.com',
                ],
            ],
        ]);

    $this->assertDatabaseHas('comments', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'body' => 'This task is ready for review.',
    ]);
});

it('returns validation errors when creating a comment with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'comment-validation@example.com',
    ]);
    [, , $task] = commentEndpointTaskForUser($user);

    commentEndpointLoginAs($user);

    $this->postJson("/api/tasks/{$task->id}/comments", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'body',
        ]);
});

it('lists comments for an accessible task', function () {
    $user = User::factory()->create([
        'email' => 'comment-index@example.com',
        'name' => 'Index Author',
    ]);
    [, , $task] = commentEndpointTaskForUser($user);
    $visibleComment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'body' => 'Visible comment body.',
    ]);

    Comment::factory()->create([
        'body' => 'Hidden comment body.',
    ]);

    commentEndpointLoginAs($user);

    $this->getJson("/api/tasks/{$task->id}/comments")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comments retrieved successfully',
        ])
        ->assertJsonFragment([
            'id' => $visibleComment->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Visible comment body.',
        ])
        ->assertJsonFragment([
            'id' => $user->id,
            'name' => 'Index Author',
            'email' => 'comment-index@example.com',
        ])
        ->assertJsonMissing([
            'body' => 'Hidden comment body.',
        ]);
});

it('shows an accessible comment', function () {
    $user = User::factory()->create([
        'email' => 'comment-show@example.com',
        'name' => 'Show Author',
    ]);
    [, , $task] = commentEndpointTaskForUser($user);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'body' => 'Visible comment.',
    ]);

    commentEndpointLoginAs($user);

    $this->getJson("/api/tasks/{$task->id}/comments/{$comment->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comment retrieved successfully',
            'data' => [
                'id' => $comment->id,
                'task_id' => $task->id,
                'user_id' => $user->id,
                'body' => 'Visible comment.',
                'author' => [
                    'id' => $user->id,
                    'name' => 'Show Author',
                    'email' => 'comment-show@example.com',
                ],
            ],
        ]);
});

it('forbids inaccessible task and comment access', function () {
    $user = User::factory()->create([
        'email' => 'comment-forbidden@example.com',
    ]);
    [, , $accessibleTask] = commentEndpointTaskForUser($user);
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
    $hiddenComment = Comment::factory()->create([
        'task_id' => $hiddenTask->id,
    ]);

    commentEndpointLoginAs($user);

    $this->getJson("/api/tasks/{$hiddenTask->id}/comments")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment access denied',
            'errors' => [
                'comment' => ['You do not have access to this comment.'],
            ],
        ]);

    $this->getJson("/api/tasks/{$accessibleTask->id}/comments/{$hiddenComment->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Comment access denied',
        ]);
});

it('allows the author to update and delete their own comment', function () {
    $author = User::factory()->create([
        'email' => 'comment-author@example.com',
    ]);
    [, , $task] = commentEndpointTaskForUser($author);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'body' => 'Before update.',
    ]);

    commentEndpointLoginAs($author);

    $this->patchJson("/api/tasks/{$task->id}/comments/{$comment->id}", [
        'body' => 'After update.',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => [
                'id' => $comment->id,
                'body' => 'After update.',
            ],
        ]);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'body' => 'After update.',
    ]);

    $this->deleteJson("/api/tasks/{$task->id}/comments/{$comment->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Comment deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('comments', [
        'id' => $comment->id,
    ]);
});

it('forbids non-authors from updating or deleting comments', function () {
    $author = User::factory()->create([
        'email' => 'comment-owner@example.com',
    ]);
    $otherUser = User::factory()->create([
        'email' => 'comment-non-author@example.com',
    ]);
    [$workspace, , $task] = commentEndpointTaskForUser($author);
    $otherTeam = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);
    $otherTeam->users()->attach($otherUser->id);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
        'body' => 'Original comment.',
    ]);

    commentEndpointLoginAs($otherUser);

    $this->patchJson("/api/tasks/{$task->id}/comments/{$comment->id}", [
        'body' => 'Non-author update.',
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
        'body' => 'Original comment.',
    ]);
    $this->assertNotSoftDeleted('comments', [
        'id' => $comment->id,
    ]);
});

it('does not expose sensitive author fields in comment responses', function () {
    $author = User::factory()->create([
        'email' => 'comment-safe-author@example.com',
    ]);
    [, , $task] = commentEndpointTaskForUser($author);
    $comment = Comment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $author->id,
    ]);

    commentEndpointLoginAs($author);

    $this->getJson("/api/tasks/{$task->id}/comments/{$comment->id}")
        ->assertOk()
        ->assertJsonMissingPath('data.author.password')
        ->assertJsonMissingPath('data.author.remember_token')
        ->assertJsonMissingPath('data.author.email_verified_at')
        ->assertJsonMissingPath('data.author.disabled_at')
        ->assertJsonMissingPath('data.author.roles')
        ->assertJsonMissingPath('data.author.permissions');

    $this->getJson("/api/tasks/{$task->id}/comments")
        ->assertOk()
        ->assertJsonMissingPath('data.0.author.password')
        ->assertJsonMissingPath('data.0.author.remember_token')
        ->assertJsonMissingPath('data.0.author.email_verified_at')
        ->assertJsonMissingPath('data.0.author.disabled_at')
        ->assertJsonMissingPath('data.0.author.roles')
        ->assertJsonMissingPath('data.0.author.permissions');
});

function commentEndpointLoginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @return array{Workspace, Project, Task}
 */
function commentEndpointTaskForUser(User $user): array
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
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'task_status_id' => $status->id,
        'created_by' => $user->id,
    ]);

    $team->users()->attach($user->id);

    return [$workspace, $project, $task];
}
