<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreProjectRequest;
use App\Http\Requests\Domain\UpdateProjectRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(string $workspace): JsonResponse
    {
        return $this->placeholder('Project index endpoint is not implemented yet');
    }

    public function store(StoreProjectRequest $request, string $workspace): JsonResponse
    {
        return $this->placeholder('Project store endpoint is not implemented yet');
    }

    public function show(string $workspace, string $project): JsonResponse
    {
        return $this->placeholder('Project show endpoint is not implemented yet');
    }

    public function update(UpdateProjectRequest $request, string $workspace, string $project): JsonResponse
    {
        return $this->placeholder('Project update endpoint is not implemented yet');
    }

    public function destroy(string $workspace, string $project): JsonResponse
    {
        return $this->placeholder('Project destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
