<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a user with the factory', function () {
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
    ]);
});

it('creates a workspace with the factory', function () {
    $workspace = Workspace::factory()->create();

    $this->assertDatabaseHas('workspaces', [
        'id' => $workspace->id,
        'slug' => $workspace->slug,
    ]);
});
