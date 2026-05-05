<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protects domain routes with Sanctum', function (string $method, string $uri) {
    $this->json($method, $uri)
        ->assertUnauthorized();
})->with([
    ['GET', '/api/workspaces'],
    ['POST', '/api/workspaces'],
    ['GET', '/api/workspaces/1/teams'],
    ['GET', '/api/workspaces/1/projects'],
    ['GET', '/api/projects/1/task-statuses'],
    ['GET', '/api/projects/1/tasks'],
    ['GET', '/api/tasks/1/comments'],
]);

it('blocks disabled authenticated users from domain routes', function () {
    $user = User::factory()->create([
        'email' => 'disabled-domain@example.com',
    ]);

    $this->postJson('/api/login', [
        'email' => 'disabled-domain@example.com',
        'password' => 'password',
    ])->assertOk();

    $user->forceFill([
        'disabled_at' => now(),
    ])->save();

    $this->getJson('/api/workspaces')
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'message' => 'Account is disabled',
            'errors' => [
                'account' => ['This account has been disabled.'],
            ],
        ]);
});
