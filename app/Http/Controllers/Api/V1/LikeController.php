<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ArticleNotPublishedException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(
        protected LikeService $likeService,
    ) {}

    public function toggle(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $result = $this->likeService->toggle($user->id, $id);

            return response()->json([
                'data' => $result,
            ]);
        } catch (ArticleNotPublishedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
