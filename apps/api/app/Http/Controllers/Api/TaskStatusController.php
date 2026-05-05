<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTaskStatusRequest;
use App\Http\Requests\Domain\UpdateTaskStatusRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TaskStatusController extends Controller
{
    public function index(string $project): JsonResponse
    {
        return $this->placeholder('Task status index endpoint is not implemented yet');
    }

    public function store(StoreTaskStatusRequest $request, string $project): JsonResponse
    {
        return $this->placeholder('Task status store endpoint is not implemented yet');
    }

    public function show(string $project, string $taskStatus): JsonResponse
    {
        return $this->placeholder('Task status show endpoint is not implemented yet');
    }

    public function update(UpdateTaskStatusRequest $request, string $project, string $taskStatus): JsonResponse
    {
        return $this->placeholder('Task status update endpoint is not implemented yet');
    }

    public function destroy(string $project, string $taskStatus): JsonResponse
    {
        return $this->placeholder('Task status destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
