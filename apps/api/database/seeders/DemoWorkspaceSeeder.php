<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            'admin' => User::query()->where('email', 'admin@demo.test')->firstOrFail(),
            'teamLead' => User::query()->where('email', 'teamlead@demo.test')->firstOrFail(),
            'senior' => User::query()->where('email', 'senior@demo.test')->firstOrFail(),
            'mid' => User::query()->where('email', 'mid@demo.test')->firstOrFail(),
            'junior' => User::query()->where('email', 'junior@demo.test')->firstOrFail(),
            'viewer' => User::query()->where('email', 'viewer@demo.test')->firstOrFail(),
        ];

        $workspace = Workspace::query()->updateOrCreate(
            ['slug' => 'scaffoldix-demo'],
            [
                'name' => 'Scaffoldix Demo Workspace',
                'description' => 'Demo workspace for local development and portfolio review.',
            ],
        );

        $team = Team::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'slug' => 'platform-team',
            ],
            [
                'name' => 'Platform Team',
                'description' => 'Demo team responsible for the main project.',
            ],
        );

        $project = Project::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'slug' => 'scaffoldix-mvp',
            ],
            [
                'team_id' => $team->id,
                'created_by' => $users['admin']->id,
                'name' => 'Scaffoldix MVP',
                'description' => 'Demo project used to showcase teams, tasks, comments, roles, and permissions.',
                'starts_at' => null,
                'ends_at' => null,
            ],
        );

        $statuses = $this->seedTaskStatuses($workspace, $project);
        $roles = $this->seedRoles($workspace);

        $this->syncRolePermissions($roles);
        $this->syncMemberships($team, $project, $users, $roles);
        $tasks = $this->seedTasks($project, $statuses, $users);
        $this->seedComments($tasks, $users);
    }

    /**
     * @return array<string, TaskStatus>
     */
    private function seedTaskStatuses(Workspace $workspace, Project $project): array
    {
        $statuses = [
            ['name' => 'Backlog', 'slug' => 'backlog', 'color' => 'gray', 'position' => 0, 'is_default' => true],
            ['name' => 'To Do', 'slug' => 'to-do', 'color' => 'blue', 'position' => 1, 'is_default' => false],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => 'yellow', 'position' => 2, 'is_default' => false],
            ['name' => 'In Review', 'slug' => 'in-review', 'color' => 'purple', 'position' => 3, 'is_default' => false],
            ['name' => 'Done', 'slug' => 'done', 'color' => 'green', 'position' => 4, 'is_default' => false],
            ['name' => 'Blocked', 'slug' => 'blocked', 'color' => 'red', 'position' => 5, 'is_default' => false],
        ];

        $seeded = [];

        foreach ($statuses as $status) {
            $seeded[$status['slug']] = TaskStatus::query()->updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'project_id' => $project->id,
                    'slug' => $status['slug'],
                ],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'position' => $status['position'],
                    'is_default' => $status['is_default'],
                ],
            );
        }

        return $seeded;
    }

    /**
     * @return array<string, Role>
     */
    private function seedRoles(Workspace $workspace): array
    {
        $roles = [
            'admin' => ['name' => 'Admin', 'slug' => 'admin', 'level' => 100],
            'teamLead' => ['name' => 'Team Lead', 'slug' => 'team-lead', 'level' => 80],
            'senior' => ['name' => 'Senior', 'slug' => 'senior', 'level' => 60],
            'mid' => ['name' => 'Mid', 'slug' => 'mid', 'level' => 40],
            'junior' => ['name' => 'Junior', 'slug' => 'junior', 'level' => 20],
            'viewer' => ['name' => 'Viewer', 'slug' => 'viewer', 'level' => 10],
        ];

        $seeded = [];

        foreach ($roles as $key => $role) {
            $seeded[$key] = Role::query()->updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'slug' => $role['slug'],
                ],
                [
                    'name' => $role['name'],
                    'level' => $role['level'],
                    'is_system' => true,
                    'description' => $role['name'].' demo role.',
                ],
            );
        }

        return $seeded;
    }

    /**
     * @param array<string, Role> $roles
     */
    private function syncRolePermissions(array $roles): void
    {
        $permissions = Permission::query()->pluck('id', 'name');

        $roles['admin']->permissions()->sync($permissions->values()->all());

        $roles['teamLead']->permissions()->sync(
            $permissions->except(['admin.access', 'user.disable'])->values()->all(),
        );

        $roles['senior']->permissions()->sync($permissions->only([
            'project.view',
            'task.view',
            'task.create',
            'task.update',
            'task.assign',
            'task.change_status',
            'comment.view',
            'comment.create',
            'comment.update_own',
            'comment.delete_own',
        ])->values()->all());

        $roles['mid']->permissions()->sync($permissions->only([
            'project.view',
            'task.view',
            'task.update',
            'task.assign',
            'task.change_status',
            'comment.view',
            'comment.create',
            'comment.update_own',
            'comment.delete_own',
        ])->values()->all());

        $roles['junior']->permissions()->sync($permissions->only([
            'project.view',
            'task.view',
            'task.change_status',
            'comment.view',
            'comment.create',
            'comment.update_own',
            'comment.delete_own',
        ])->values()->all());

        $roles['viewer']->permissions()->sync($permissions->only([
            'project.view',
            'task.view',
            'comment.view',
        ])->values()->all());
    }

    /**
     * @param array<string, User> $users
     * @param array<string, Role> $roles
     */
    private function syncMemberships(Team $team, Project $project, array $users, array $roles): void
    {
        $userIds = collect($users)->pluck('id')->all();

        $team->users()->syncWithoutDetaching($userIds);
        $project->users()->syncWithoutDetaching($userIds);

        foreach ($users as $key => $user) {
            DB::table('team_user_role')->updateOrInsert(
                [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'role_id' => $roles[$key]->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    /**
     * @param array<string, TaskStatus> $statuses
     * @param array<string, User> $users
     *
     * @return array<string, Task>
     */
    private function seedTasks(Project $project, array $statuses, array $users): array
    {
        $tasks = [
            'foundation' => [
                'title' => 'Set up Laravel API foundation',
                'status' => 'done',
                'assigned_to' => $users['senior']->id,
                'priority' => 'high',
            ],
            'schema' => [
                'title' => 'Create database schema',
                'status' => 'done',
                'assigned_to' => $users['senior']->id,
                'priority' => 'high',
            ],
            'permissions' => [
                'title' => 'Configure role permissions',
                'status' => 'in-review',
                'assigned_to' => $users['mid']->id,
                'priority' => 'medium',
            ],
            'dashboard' => [
                'title' => 'Build project dashboard',
                'status' => 'in-progress',
                'assigned_to' => $users['mid']->id,
                'priority' => 'medium',
            ],
            'juniorRestrictions' => [
                'title' => 'Test junior task restrictions',
                'status' => 'to-do',
                'assigned_to' => $users['junior']->id,
                'priority' => 'low',
            ],
        ];

        $seeded = [];

        foreach ($tasks as $key => $task) {
            $seeded[$key] = Task::query()->updateOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => $task['title'],
                ],
                [
                    'task_status_id' => $statuses[$task['status']]->id,
                    'created_by' => $users['admin']->id,
                    'assigned_to' => $task['assigned_to'],
                    'description' => 'Demo task for local development and portfolio review.',
                    'priority' => $task['priority'],
                    'due_date' => null,
                ],
            );
        }

        return $seeded;
    }

    /**
     * @param array<string, Task> $tasks
     * @param array<string, User> $users
     */
    private function seedComments(array $tasks, array $users): void
    {
        Comment::query()->updateOrCreate(
            [
                'task_id' => $tasks['permissions']->id,
                'user_id' => $users['teamLead']->id,
                'body' => 'Please confirm the role permissions match the demo workflow.',
            ],
            [],
        );

        Comment::query()->updateOrCreate(
            [
                'task_id' => $tasks['juniorRestrictions']->id,
                'user_id' => $users['junior']->id,
                'body' => 'I will verify the junior role can only update the intended task fields.',
            ],
            [],
        );
    }
}
