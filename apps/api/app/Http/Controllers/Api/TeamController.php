<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTeamRequest;
use App\Http\Requests\Domain\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\Workspace;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $teams = $workspace->teams()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            TeamResource::collection($teams),
            'Teams retrieved successfully'
        );
    }

    public function store(StoreTeamRequest $request, string $workspace): JsonResponse
    {
        $workspace = $this->accessibleWorkspace($request, $workspace);

        if (! $workspace) {
            return $this->forbidden();
        }

        $team = $workspace->teams()->create($request->validated());

        return ApiResponse::success(
            new TeamResource($team),
            'Team created successfully',
            201
        );
    }

    public function show(Request $request, string $workspace, string $team): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new TeamResource($team),
            'Team retrieved successfully'
        );
    }

    public function update(UpdateTeamRequest $request, string $workspace, string $team): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        $team->update($request->validated());

        return ApiResponse::success(
            new TeamResource($team),
            'Team updated successfully'
        );
    }

    public function destroy(Request $request, string $workspace, string $team): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        $team->delete();

        return ApiResponse::success(
            null,
            'Team deleted successfully'
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

    private function accessibleTeam(Request $request, string $workspace, string $team): ?Team
    {
        return Team::query()
            ->whereKey($team)
            ->where('workspace_id', $workspace)
            ->whereHas('workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Team access denied',
            [
                'team' => ['You do not have access to this team.'],
            ],
            403
        );
    }
}
