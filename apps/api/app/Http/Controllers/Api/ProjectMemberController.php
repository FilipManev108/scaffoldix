<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreProjectMemberRequest;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function index(Request $request, string $workspace, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $members = $project->users()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            UserResource::collection($members),
            'Project members retrieved successfully'
        );
    }

    public function store(StoreProjectMemberRequest $request, string $workspace, string $project): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $project->users()->attach($request->validated('user_id'));

        $member = $project->users()
            ->whereKey($request->validated('user_id'))
            ->firstOrFail();

        return ApiResponse::success(
            new UserResource($member),
            'Project member added successfully',
            201
        );
    }

    public function destroy(Request $request, string $workspace, string $project, string $user): JsonResponse
    {
        $project = $this->accessibleProject($request, $workspace, $project);

        if (! $project) {
            return $this->forbidden();
        }

        $project->users()->detach($user);

        return ApiResponse::success(
            null,
            'Project member removed successfully'
        );
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
