<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTaskStatusRequest;
use App\Http\Requests\Domain\UpdateTaskStatusRequest;
use App\Http\Resources\TaskStatusResource;
use App\Models\Project;
use App\Models\TaskStatus;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    public function index(Request $request, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $statuses = $project->taskStatuses()
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            TaskStatusResource::collection($statuses),
            'Task statuses retrieved successfully'
        );
    }

    public function store(StoreTaskStatusRequest $request, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $status = $project->taskStatuses()->create([
            ...$request->validated(),
            'workspace_id' => $project->workspace_id,
        ]);

        return ApiResponse::success(
            new TaskStatusResource($status),
            'Task status created successfully',
            201
        );
    }

    public function show(Request $request, string $project, string $taskStatus): JsonResponse
    {
        $status = $this->accessibleTaskStatus($request, $project, $taskStatus);

        if (! $status) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new TaskStatusResource($status),
            'Task status retrieved successfully'
        );
    }

    public function update(UpdateTaskStatusRequest $request, string $project, string $taskStatus): JsonResponse
    {
        $status = $this->accessibleTaskStatus($request, $project, $taskStatus);

        if (! $status) {
            return $this->forbidden();
        }

        $status->update($request->validated());

        return ApiResponse::success(
            new TaskStatusResource($status),
            'Task status updated successfully'
        );
    }

    public function destroy(Request $request, string $project, string $taskStatus): JsonResponse
    {
        $status = $this->accessibleTaskStatus($request, $project, $taskStatus);

        if (! $status) {
            return $this->forbidden();
        }

        $status->delete();

        return ApiResponse::success(
            null,
            'Task status deleted successfully'
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

    private function accessibleTaskStatus(Request $request, string $project, string $taskStatus): ?TaskStatus
    {
        return TaskStatus::query()
            ->whereKey($taskStatus)
            ->where('project_id', $project)
            ->whereHas('project.workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Task status access denied',
            [
                'task_status' => ['You do not have access to this task status.'],
            ],
            403
        );
    }
}
