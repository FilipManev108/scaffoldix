<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreCommentRequest;
use App\Http\Requests\Domain\UpdateCommentRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(string $task): JsonResponse
    {
        return $this->placeholder('Comment index endpoint is not implemented yet');
    }

    public function store(StoreCommentRequest $request, string $task): JsonResponse
    {
        return $this->placeholder('Comment store endpoint is not implemented yet');
    }

    public function show(string $task, string $comment): JsonResponse
    {
        return $this->placeholder('Comment show endpoint is not implemented yet');
    }

    public function update(UpdateCommentRequest $request, string $task, string $comment): JsonResponse
    {
        return $this->placeholder('Comment update endpoint is not implemented yet');
    }

    public function destroy(string $task, string $comment): JsonResponse
    {
        return $this->placeholder('Comment destroy endpoint is not implemented yet');
    }

    private function placeholder(string $message): JsonResponse
    {
        return ApiResponse::error($message, [], 501);
    }
}
