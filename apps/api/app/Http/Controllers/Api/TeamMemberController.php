<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTeamMemberRequest;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function index(Request $request, string $workspace, string $team): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        $members = $team->users()
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            UserResource::collection($members),
            'Team members retrieved successfully'
        );
    }

    public function store(StoreTeamMemberRequest $request, string $workspace, string $team): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        $team->users()->attach($request->validated('user_id'));

        $member = $team->users()
            ->whereKey($request->validated('user_id'))
            ->firstOrFail();

        return ApiResponse::success(
            new UserResource($member),
            'Team member added successfully',
            201
        );
    }

    public function destroy(Request $request, string $workspace, string $team, string $user): JsonResponse
    {
        $team = $this->accessibleTeam($request, $workspace, $team);

        if (! $team) {
            return $this->forbidden();
        }

        $team->users()->detach($user);

        return ApiResponse::success(
            null,
            'Team member removed successfully'
        );
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
