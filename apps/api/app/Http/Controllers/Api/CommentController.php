<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Domain\StoreCommentRequest;
use App\Http\Requests\Domain\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, string $task): JsonResponse
    {
        $task = $this->accessibleTask($request, $task);

        if (! $task) {
            return $this->forbidden();
        }

        $comments = $task->comments()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return ApiResponse::success(
            CommentResource::collection($comments),
            'Comments retrieved successfully'
        );
    }

    public function store(StoreCommentRequest $request, string $task): JsonResponse
    {
        $task = $this->accessibleTask($request, $task);

        if (! $task) {
            return $this->forbidden();
        }

        $comment = $task->comments()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return ApiResponse::success(
            new CommentResource($comment->load('user')),
            'Comment created successfully',
            201
        );
    }

    public function show(Request $request, string $task, string $comment): JsonResponse
    {
        $comment = $this->accessibleComment($request, $task, $comment);

        if (! $comment) {
            return $this->forbidden();
        }

        return ApiResponse::success(
            new CommentResource($comment->load('user')),
            'Comment retrieved successfully'
        );
    }

    public function update(UpdateCommentRequest $request, string $task, string $comment): JsonResponse
    {
        $comment = $this->accessibleComment($request, $task, $comment);

        if (! $comment) {
            return $this->forbidden();
        }

        if (! $this->isAuthor($request, $comment)) {
            return $this->authorForbidden();
        }

        $comment->update($request->validated());

        return ApiResponse::success(
            new CommentResource($comment->load('user')),
            'Comment updated successfully'
        );
    }

    public function destroy(Request $request, string $task, string $comment): JsonResponse
    {
        $comment = $this->accessibleComment($request, $task, $comment);

        if (! $comment) {
            return $this->forbidden();
        }

        if (! $this->isAuthor($request, $comment)) {
            return $this->authorForbidden();
        }

        $comment->delete();

        return ApiResponse::success(
            null,
            'Comment deleted successfully'
        );
    }

    private function accessibleTask(Request $request, string $task): ?Task
    {
        return Task::query()
            ->whereKey($task)
            ->whereHas('project.workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function accessibleComment(Request $request, string $task, string $comment): ?Comment
    {
        return Comment::query()
            ->whereKey($comment)
            ->where('task_id', $task)
            ->whereHas('task.project.workspace.teams.users', function ($query) use ($request): void {
                $query->whereKey($request->user()->id);
            })
            ->first();
    }

    private function isAuthor(Request $request, Comment $comment): bool
    {
        return $comment->user_id === $request->user()->id;
    }

    private function forbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Comment access denied',
            [
                'comment' => ['You do not have access to this comment.'],
            ],
            403
        );
    }

    private function authorForbidden(): JsonResponse
    {
        return ApiResponse::error(
            'Comment author required',
            [
                'comment' => ['Only the comment author can modify this comment.'],
            ],
            403
        );
    }
}
