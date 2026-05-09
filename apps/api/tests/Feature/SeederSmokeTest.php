<?php

use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function expectedSeededPermissionNames(): array
{
    return [
        'workspace.view',
        'workspace.create',
        'workspace.update',
        'workspace.delete',
        'team.view',
        'team.create',
        'team.update',
        'team.delete',
        'team_member.view',
        'team_member.create',
        'team_member.delete',
        'project.view',
        'project.create',
        'project.update',
        'project.delete',
        'project_member.view',
        'project_member.create',
        'project_member.delete',
        'task_status.view',
        'task_status.create',
        'task_status.update',
        'task_status.delete',
        'task.view',
        'task.create',
        'task.update',
        'task.delete',
        'comment.view',
        'comment.create',
        'comment.update',
        'comment.delete',
        'role.view',
        'role.create',
        'role.update',
        'role.delete',
        'permission.view',
    ];
}

function sortedPermissionNames(array $permissions): array
{
    return collect($permissions)->sort()->values()->all();
}

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
    $this->assertDatabaseHas('permissions', ['name' => 'permission.view']);
});

it('seeds the expected permission catalog repeatably', function () {
    $expectedPermissions = sortedPermissionNames(expectedSeededPermissionNames());

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $seededPermissions = Permission::query()
        ->pluck('name')
        ->sort()
        ->values()
        ->all();

    expect($seededPermissions)->toBe($expectedPermissions)
        ->and(Permission::query()->count())->toBe(count($expectedPermissions))
        ->and(Permission::query()->select('name')->distinct()->count())->toBe(count($expectedPermissions));
});

it('seeds demo roles with expected permission assignments', function () {
    $this->seed(DatabaseSeeder::class);

    $allPermissions = sortedPermissionNames(expectedSeededPermissionNames());

    $expectedByRole = [
        'admin' => $allPermissions,
        'team-lead' => sortedPermissionNames([
            'workspace.view',
            'workspace.create',
            'workspace.update',
            'team.view',
            'team.create',
            'team.update',
            'team.delete',
            'team_member.view',
            'team_member.create',
            'team_member.delete',
            'project.view',
            'project.create',
            'project.update',
            'project.delete',
            'project_member.view',
            'project_member.create',
            'project_member.delete',
            'task_status.view',
            'task_status.create',
            'task_status.update',
            'task_status.delete',
            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'comment.view',
            'comment.create',
            'comment.update',
            'comment.delete',
            'role.view',
            'permission.view',
        ]),
        'senior' => sortedPermissionNames([
            'project.view',
            'task_status.view',
            'task_status.create',
            'task_status.update',
            'task_status.delete',
            'task.view',
            'task.create',
            'task.update',
            'task.delete',
            'comment.view',
            'comment.create',
            'comment.update',
            'comment.delete',
        ]),
        'mid' => sortedPermissionNames([
            'project.view',
            'task_status.view',
            'task.view',
            'task.create',
            'task.update',
            'comment.view',
            'comment.create',
            'comment.update',
            'comment.delete',
        ]),
        'junior' => sortedPermissionNames([
            'project.view',
            'task_status.view',
            'task.view',
            'task.update',
            'comment.view',
            'comment.create',
            'comment.update',
        ]),
        'viewer' => sortedPermissionNames([
            'workspace.view',
            'team.view',
            'team_member.view',
            'project.view',
            'project_member.view',
            'task_status.view',
            'task.view',
            'comment.view',
        ]),
    ];

    foreach ($expectedByRole as $slug => $expectedPermissions) {
        $role = Role::query()->where('slug', $slug)->first();

        expect($role)->not->toBeNull()
            ->and(
                $role->permissions()
                    ->pluck('name')
                    ->sort()
                    ->values()
                    ->all()
            )->toBe($expectedPermissions);
    }
});

it('keeps admin and viewer permission boundaries clear', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = Role::query()->where('slug', 'admin')->firstOrFail();
    $viewer = Role::query()->where('slug', 'viewer')->firstOrFail();

    $adminPermissionNames = $admin->permissions()->pluck('name')->all();
    $viewerPermissionNames = $viewer->permissions()->pluck('name')->all();
    $viewerHasOnlyViewPermissions = collect($viewerPermissionNames)
        ->every(fn (string $permission): bool => str_ends_with($permission, '.view'));

    expect(sortedPermissionNames($adminPermissionNames))->toBe(sortedPermissionNames(expectedSeededPermissionNames()))
        ->and($viewerHasOnlyViewPermissions)->toBeTrue()
        ->and($viewerPermissionNames)->not->toContain('role.view')
        ->and($viewerPermissionNames)->not->toContain('permission.view');
});

it('keeps lower demo roles away from role and permission management', function () {
    $this->seed(DatabaseSeeder::class);

    foreach (['senior', 'mid', 'junior', 'viewer'] as $slug) {
        $permissionNames = Role::query()
            ->where('slug', $slug)
            ->firstOrFail()
            ->permissions()
            ->pluck('name')
            ->all();

        expect($permissionNames)->not->toContain(
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
            'permission.view',
        );
    }

    $rolePermissionPairs = DB::table('role_permission')
        ->select('role_id', 'permission_id')
        ->distinct()
        ->count();

    expect(DB::table('role_permission')->count())->toBe($rolePermissionPairs);
});
