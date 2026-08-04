<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateCommentDTO;
use App\DTOs\UpdateCommentDTO;
use App\Exceptions\ArticleNotPublishedException;
use App\Exceptions\MaxCommentDepthExceededException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected CommentService $commentService,
    ) {}

    public function index(int $id): AnonymousResourceCollection|JsonResponse
    {
        try {
            $comments = $this->commentService->getPaginatedByArticle($id);

            return CommentResource::collection($comments);
        } catch (ArticleNotPublishedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(StoreCommentRequest $request, int $id): JsonResponse
    {
        $dto = CreateCommentDTO::fromRequest($request, $id);

        try {
            $comment = $this->commentService->create($dto);

            return (new CommentResource($comment))
                ->response()
                ->setStatusCode(201);
        } catch (ArticleNotPublishedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function reply(StoreCommentRequest $request, int $id): JsonResponse
    {
        $parent = $this->commentService->findById($id);

        if (! $parent) {
            return response()->json([
                'message' => 'Comentário não encontrado.',
            ], 404);
        }

        $dto = CreateCommentDTO::fromRequest($request, $parent->article_id, $parent->id);

        try {
            $comment = $this->commentService->create($dto);

            return (new CommentResource($comment))
                ->response()
                ->setStatusCode(201);
        } catch (ArticleNotPublishedException|MaxCommentDepthExceededException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        $comment = $this->commentService->findById($id);

        if (! $comment) {
            return response()->json([
                'message' => 'Comentário não encontrado.',
            ], 404);
        }

        $this->authorize('update', $comment);

        $dto = UpdateCommentDTO::fromRequest($request);
        $updated = $this->commentService->update($comment, $dto);

        return (new CommentResource($updated))
            ->response()
            ->setStatusCode(200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $comment = $this->commentService->findById($id);

        if (! $comment) {
            return response()->json([
                'message' => 'Comentário não encontrado.',
            ], 404);
        }

        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json(null, 204);
    }
}
