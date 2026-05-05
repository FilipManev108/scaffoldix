<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreProjectRequest;
use App\Http\Requests\Domain\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $projects = $workspace->projects()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            ProjectResource::collection($projects),
            'Projects retrieved successfully'
        );
    }

    public function store(StoreProjectRequest $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $project = $workspace->projects()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return ApiResponse::success(
            new ProjectResource($project),
            'Project created successfully',
            201
        );
    }

    public function show(Request $request, string $workspace, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new ProjectResource($project),
            'Project retrieved successfully'
        );
    }

    public function update(UpdateProjectRequest $request, string $workspace, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $project->update($request->validated());

        return ApiResponse::success(
            new ProjectResource($project),
            'Project updated successfully'
        );
    }

    public function destroy(Request $request, string $workspace, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $project->delete();

        return ApiResponse::success(
            null,
            'Project deleted successfully'
        );
    }

    private function accessibleWorkspace(Request $request, string $workspace): ?Workspace
    {
        return Workspace::query()
            ->whereKey($workspace)
            ->whereHas('teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function accessibleProject(Request $request, string $workspace, string $project): ?Project
    {
        return Project::query()
            ->whereKey($project)
            ->where('workspace_id', $workspace)
            ->whereHas('workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Project access denied',
            [
                'project' => ['You do not have access to this project.'],
            ],
            403
        );
    }
}
