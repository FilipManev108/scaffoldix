<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects workspace routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces'],
    ['POST', '/api/workspaces'],
    ['GET', '/api/workspaces/1'],
    ['PATCH', '/api/workspaces/1'],
    ['DELETE', '/api/workspaces/1'],
]);

it('creates a workspace and default team for the authenticated user', function () {
    $user = User::factory()->create([
        'email' => 'workspace-create@example.com',
    ]);

    loginAs($user);

    $this->postJson('/api/workspaces', [
        'name' => 'Product Engineering',
        'slug' => 'product-engineering',
        'description' => 'Internal product delivery workspace.',
    ])
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Workspace created successfully',
            'data' => [
                'name' => 'Product Engineering',
                'slug' => 'product-engineering',
            ],
        ])
        ->assertJsonMissingPath('data.description');

    $workspace = Workspace::where('slug', 'product-engineering')->firstOrFail();

    $this->assertDatabaseHas('teams', [
        'workspace_id' => $workspace->id,
        'name' => 'Default Team',
        'slug' => 'default-team',
    ]);

    $team = Team::where('workspace_id', $workspace->id)->firstOrFail();

    $this->assertDatabaseHas('team_user', [
        'team_id' => $team->id,
        'user_id' => $user->id,
    ]);
});

it('returns validation errors when creating a workspace with invalid data', function () {
    $user = User::factory()->create([
        'email' => 'workspace-validation@example.com',
    ]);

    loginAs($user);

    $this->postJson('/api/workspaces', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'slug',
        ]);
});

it('rejects duplicate workspace slugs', function () {
    Workspace::factory()->create([
        'slug' => 'duplicate-workspace',
    ]);

    $user = User::factory()->create([
        'email' => 'workspace-duplicate@example.com',
    ]);

    loginAs($user);

    $this->postJson('/api/workspaces', [
        'name' => 'Duplicate Workspace',
        'slug' => 'duplicate-workspace',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'slug',
        ]);
});

it('lists only workspaces accessible through team membership', function () {
    $user = User::factory()->create([
        'email' => 'workspace-index@example.com',
    ]);

    $accessible = workspaceForUser($user, [
        'name' => 'Accessible Workspace',
        'slug' => 'accessible-workspace',
    ]);

    Workspace::factory()->create([
        'name' => 'Hidden Workspace',
        'slug' => 'hidden-workspace',
    ]);

    loginAs($user);

    $this->getJson('/api/workspaces')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Workspaces retrieved successfully',
            'data' => [
                [
                    'id' => $accessible->id,
                    'name' => 'Accessible Workspace',
                    'slug' => 'accessible-workspace',
                ],
            ],
        ])
        ->assertJsonMissing([
            'slug' => 'hidden-workspace',
        ]);
});

it('shows an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-show@example.com',
    ]);

    $workspace = workspaceForUser($user, [
        'name' => 'Visible Workspace',
        'slug' => 'visible-workspace',
    ]);

    loginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Workspace retrieved successfully',
            'data' => [
                'id' => $workspace->id,
                'name' => 'Visible Workspace',
                'slug' => 'visible-workspace',
            ],
        ])
        ->assertJsonMissingPath('data.description');
});

it('forbids showing an inaccessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-show-forbidden@example.com',
    ]);

    $workspace = Workspace::factory()->create();

    loginAs($user);

    $this->getJson("/api/workspaces/{$workspace->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Workspace access denied',
            'errors' => [
                'workspace' => ['You do not have access to this workspace.'],
            ],
        ]);
});

it('updates an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-update@example.com',
    ]);

    $workspace = workspaceForUser($user, [
        'slug' => 'before-update',
    ]);

    loginAs($user);

    $this->patchJson("/api/workspaces/{$workspace->id}", [
        'name' => 'After Update',
        'slug' => 'after-update',
    ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Workspace updated successfully',
            'data' => [
                'id' => $workspace->id,
                'name' => 'After Update',
                'slug' => 'after-update',
            ],
        ]);

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'After Update',
        'slug' => 'after-update',
    ]);
});

it('forbids updating an inaccessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-update-forbidden@example.com',
    ]);

    $workspace = Workspace::factory()->create([
        'name' => 'Do Not Touch',
    ]);

    loginAs($user);

    $this->patchJson("/api/workspaces/{$workspace->id}", [
        'name' => 'Changed Anyway',
    ])
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Workspace access denied',
        ]);

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'name' => 'Do Not Touch',
    ]);
});

it('soft deletes an accessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-delete@example.com',
    ]);

    $workspace = workspaceForUser($user);

    loginAs($user);

    $this->deleteJson("/api/workspaces/{$workspace->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Workspace deleted successfully',
            'data' => null,
        ]);

    $this->assertSoftDeleted('workspaces', [
        'id' => $workspace->id,
    ]);
});

it('forbids deleting an inaccessible workspace', function () {
    $user = User::factory()->create([
        'email' => 'workspace-delete-forbidden@example.com',
    ]);

    $workspace = Workspace::factory()->create();

    loginAs($user);

    $this->deleteJson("/api/workspaces/{$workspace->id}")
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Workspace access denied',
        ]);

    $this->assertNotSoftDeleted('workspaces', [
        'id' => $workspace->id,
    ]);
});

function loginAs(User $user): void
{
    test()->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

/**
 * @param array<string, mixed> $attributes
 */
function workspaceForUser(User $user, array $attributes = []): Workspace
{
    $workspace = Workspace::factory()->create($attributes);
    $team = Team::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $team->users()->attach($user->id);

    return $workspace;
}
