<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTeamRequest;
use App\Http\Requests\Domain\UpdateTeamRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function index(string $workspace): JsonResponse
    {
        return $this->placeholder('Team index endpoint is not implemented yet');
    }

    public function store(StoreTeamRequest $request, string $workspace): JsonResponse
    {
        return $this->placeholder('Team store endpoint is not implemented yet');
    }

    public function show(string $workspace, string $team): JsonResponse
    {
        return $this->placeholder('Team show endpoint is not implemented yet');
    }

    public function update(UpdateTeamRequest $request, string $workspace, string $team): JsonResponse
    {
        return $this->placeholder('Team update endpoint is not implemented yet');
    }

    public function destroy(string $workspace, string $team): JsonResponse
    {
        return $this->placeholder('Team destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
