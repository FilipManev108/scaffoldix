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
            ['name' => 'workspace.create', 'group' => 'workspace', 'description' => 'Create workspaces.'],
            ['name' => 'workspace.update', 'group' => 'workspace', 'description' => 'Update workspaces.'],
            ['name' => 'workspace.delete', 'group' => 'workspace', 'description' => 'Delete workspaces.'],
            ['name' => 'team.view', 'group' => 'team', 'description' => 'View teams.'],
            ['name' => 'team.create', 'group' => 'team', 'description' => 'Create teams.'],
            ['name' => 'team.update', 'group' => 'team', 'description' => 'Update teams.'],
            ['name' => 'team.delete', 'group' => 'team', 'description' => 'Delete teams.'],
            ['name' => 'team_member.view', 'group' => 'team_member', 'description' => 'View team members.'],
            ['name' => 'team_member.create', 'group' => 'team_member', 'description' => 'Add team members.'],
            ['name' => 'team_member.delete', 'group' => 'team_member', 'description' => 'Remove team members.'],
            ['name' => 'project.view', 'group' => 'project', 'description' => 'View projects.'],
            ['name' => 'project.create', 'group' => 'project', 'description' => 'Create projects.'],
            ['name' => 'project.update', 'group' => 'project', 'description' => 'Update projects.'],
            ['name' => 'project.delete', 'group' => 'project', 'description' => 'Delete projects.'],
            ['name' => 'project_member.view', 'group' => 'project_member', 'description' => 'View project members.'],
            ['name' => 'project_member.create', 'group' => 'project_member', 'description' => 'Add project members.'],
            ['name' => 'project_member.delete', 'group' => 'project_member', 'description' => 'Remove project members.'],
            ['name' => 'task_status.view', 'group' => 'task_status', 'description' => 'View task statuses.'],
            ['name' => 'task_status.create', 'group' => 'task_status', 'description' => 'Create task statuses.'],
            ['name' => 'task_status.update', 'group' => 'task_status', 'description' => 'Update task statuses.'],
            ['name' => 'task_status.delete', 'group' => 'task_status', 'description' => 'Delete task statuses.'],
            ['name' => 'task.view', 'group' => 'task', 'description' => 'View tasks.'],
            ['name' => 'task.create', 'group' => 'task', 'description' => 'Create tasks.'],
            ['name' => 'task.update', 'group' => 'task', 'description' => 'Update tasks.'],
            ['name' => 'task.delete', 'group' => 'task', 'description' => 'Delete tasks.'],
            ['name' => 'comment.view', 'group' => 'comment', 'description' => 'View comments.'],
            ['name' => 'comment.create', 'group' => 'comment', 'description' => 'Create comments.'],
            ['name' => 'comment.update', 'group' => 'comment', 'description' => 'Update comments.'],
            ['name' => 'comment.delete', 'group' => 'comment', 'description' => 'Delete comments.'],
            ['name' => 'role.view', 'group' => 'role', 'description' => 'View roles.'],
            ['name' => 'role.create', 'group' => 'role', 'description' => 'Create roles.'],
            ['name' => 'role.update', 'group' => 'role', 'description' => 'Update roles.'],
            ['name' => 'role.delete', 'group' => 'role', 'description' => 'Delete roles.'],
            ['name' => 'permission.view', 'group' => 'permission', 'description' => 'View permissions.'],
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
