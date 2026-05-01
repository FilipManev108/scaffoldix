<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the demo backend foundation data', function () {
    $this->seed(DatabaseSeeder::class);

    foreach ([
        'admin@demo.test',
        'teamlead@demo.test',
        'senior@demo.test',
        'mid@demo.test',
        'junior@demo.test',
        'viewer@demo.test',
    ] as $email) {
        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    $this->assertDatabaseHas('workspaces', ['slug' => 'scaffoldix-demo']);
    $this->assertDatabaseHas('projects', ['slug' => 'scaffoldix-mvp']);
    $this->assertDatabaseHas('permissions', ['name' => 'admin.access']);
});
