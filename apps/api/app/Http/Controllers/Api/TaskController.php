<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTaskRequest;
use App\Http\Requests\Domain\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $tasks = $project->tasks()
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success(
            TaskResource::collection($tasks),
            'Tasks retrieved successfully'
        );
    }

    public function store(StoreTaskRequest $request, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $task = $project->tasks()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return ApiResponse::success(
            new TaskResource($task),
            'Task created successfully',
            201
        );
    }

    public function show(Request $request, string $project, string $task): JsonResponse
    {
        $task = $this->accessibleTask($request, $project, $task);

        if (! $task) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new TaskResource($task),
            'Task retrieved successfully'
        );
    }

    public function update(UpdateTaskRequest $request, string $project, string $task): JsonResponse
    {
        $task = $this->accessibleTask($request, $project, $task);

        if (! $task) {
            return $this->forbidden();
        }

        $task->update($request->validated());

        return ApiResponse::success(
            new TaskResource($task),
            'Task updated successfully'
        );
    }

    public function destroy(Request $request, string $project, string $task): JsonResponse
    {
        $task = $this->accessibleTask($request, $project, $task);

        if (! $task) {
            return $this->forbidden();
        }

        $task->delete();

        return ApiResponse::success(
            null,
            'Task deleted successfully'
        );
    }

    private function accessibleProject(Request $request, string $project): ?Project
    {
        return Project::query()
            ->whereKey($project)
            ->whereHas('workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function accessibleTask(Request $request, string $project, string $task): ?Task
    {
        return Task::query()
            ->whereKey($task)
            ->where('project_id', $project)
            ->whereHas('project.workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Task access denied',
            [
                'task' => ['You do not have access to this task.'],
            ],
            403
        );
    }
}
