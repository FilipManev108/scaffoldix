<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreWorkspaceRequest;
use App\Http\Requests\Domain\UpdateWorkspaceRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->placeholder('Workspace index endpoint is not implemented yet');
    }

    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        return $this->placeholder('Workspace store endpoint is not implemented yet');
    }

    public function show(string $workspace): JsonResponse
    {
        return $this->placeholder('Workspace show endpoint is not implemented yet');
    }

    public function update(UpdateWorkspaceRequest $request, string $workspace): JsonResponse
    {
        return $this->placeholder('Workspace update endpoint is not implemented yet');
    }

    public function destroy(string $workspace): JsonResponse
    {
        return $this->placeholder('Workspace destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
