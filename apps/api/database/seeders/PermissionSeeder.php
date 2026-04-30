<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'workspace.view', 'group' => 'workspace', 'description' => 'View workspace details.'],
            ['name' => 'team.view', 'group' => 'team', 'description' => 'View teams.'],
            ['name' => 'team.create', 'group' => 'team', 'description' => 'Create teams.'],
            ['name' => 'team.update', 'group' => 'team', 'description' => 'Update teams.'],
            ['name' => 'team.delete', 'group' => 'team', 'description' => 'Delete teams.'],
            ['name' => 'project.view', 'group' => 'project', 'description' => 'View projects.'],
            ['name' => 'project.create', 'group' => 'project', 'description' => 'Create projects.'],
            ['name' => 'project.update', 'group' => 'project', 'description' => 'Update projects.'],
            ['name' => 'project.delete', 'group' => 'project', 'description' => 'Delete projects.'],
            ['name' => 'task.view', 'group' => 'task', 'description' => 'View tasks.'],
            ['name' => 'task.create', 'group' => 'task', 'description' => 'Create tasks.'],
            ['name' => 'task.update', 'group' => 'task', 'description' => 'Update tasks.'],
            ['name' => 'task.delete', 'group' => 'task', 'description' => 'Delete tasks.'],
            ['name' => 'task.assign', 'group' => 'task', 'description' => 'Assign tasks.'],
            ['name' => 'task.change_status', 'group' => 'task', 'description' => 'Change task status.'],
            ['name' => 'comment.view', 'group' => 'comment', 'description' => 'View comments.'],
            ['name' => 'comment.create', 'group' => 'comment', 'description' => 'Create comments.'],
            ['name' => 'comment.update_own', 'group' => 'comment', 'description' => 'Update own comments.'],
            ['name' => 'comment.delete_own', 'group' => 'comment', 'description' => 'Delete own comments.'],
            ['name' => 'comment.delete_any', 'group' => 'comment', 'description' => 'Delete any comment.'],
            ['name' => 'role.view', 'group' => 'role', 'description' => 'View roles.'],
            ['name' => 'role.create', 'group' => 'role', 'description' => 'Create roles.'],
            ['name' => 'role.update', 'group' => 'role', 'description' => 'Update roles.'],
            ['name' => 'role.delete', 'group' => 'role', 'description' => 'Delete roles.'],
            ['name' => 'role.assign', 'group' => 'role', 'description' => 'Assign roles.'],
            ['name' => 'admin.access', 'group' => 'admin', 'description' => 'Access admin tools.'],
            ['name' => 'user.disable', 'group' => 'user', 'description' => 'Disable users.'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission['name']],
                [
                    'group' => $permission['group'],
                    'description' => $permission['description'],
                ],
            );
        }
    }
}
