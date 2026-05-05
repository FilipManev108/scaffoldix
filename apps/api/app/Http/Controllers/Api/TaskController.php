<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreTaskRequest;
use App\Http\Requests\Domain\UpdateTaskRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(string $project): JsonResponse
    {
        return $this->placeholder('Task index endpoint is not implemented yet');
    }

    public function store(StoreTaskRequest $request, string $project): JsonResponse
    {
        return $this->placeholder('Task store endpoint is not implemented yet');
    }

    public function show(string $project, string $task): JsonResponse
    {
        return $this->placeholder('Task show endpoint is not implemented yet');
    }

    public function update(UpdateTaskRequest $request, string $project, string $task): JsonResponse
    {
        return $this->placeholder('Task update endpoint is not implemented yet');
    }

    public function destroy(string $project, string $task): JsonResponse
    {
        return $this->placeholder('Task destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
