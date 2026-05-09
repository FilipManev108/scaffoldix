<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreWorkspaceRequest;
use App\Http\Requests\Domain\UpdateWorkspaceRequest;
use App\Http\Resources\WorkspaceResource;
use App\Models\Workspace;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workspaces = Workspace::query()
            ->whereHas('teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            WorkspaceResource::collection($workspaces),
            'Workspaces retrieved successfully'
        );
    }

    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        $workspace = DB::transaction(function () use ($request): Workspace {
            $workspace = Workspace::create($request->validated());

            $team = $workspace->teams()->create([
                'name' => 'Default Team',
                'slug' => 'default-team',
                'description' => 'Default team for workspace members.',
            ]);

            $team->users()->attach($request->user()->id);

            return $workspace;
        });

        return ApiResponse::success(
            new WorkspaceResource($workspace),
            'Workspace created successfully',
            201
        );
    }

    public function show(Request $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new WorkspaceResource($workspace),
            'Workspace retrieved successfully'
        );
    }

    public function update(UpdateWorkspaceRequest $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $workspace->update($request->validated());

        return ApiResponse::success(
            new WorkspaceResource($workspace),
            'Workspace updated successfully'
        );
    }

    public function destroy(Request $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $workspace->delete();

        return ApiResponse::success(
            null,
            'Workspace deleted successfully'
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

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Workspace access denied',
            [
                'workspace' => ['You do not have access to this workspace.'],
            ],
            403
        );
    }
}
