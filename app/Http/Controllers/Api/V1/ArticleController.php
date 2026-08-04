<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ArticleQueryDTO;
use App\DTOs\CreateArticleDTO;
use App\DTOs\UpdateArticleDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected ArticleService $service
    ) {}

    public function index(Request $request): ArticleCollection
    {
        $queryDto = ArticleQueryDTO::fromArray($request->all());
        $articles = $this->service->getPaginated($queryDto);

        return new ArticleCollection($articles);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $this->authorize('create', Article::class);

        $dto = CreateArticleDTO::fromArray($request->validated(), $request->user()->id);
        $article = $this->service->create($dto);

        return (new ArticleResource($article))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(int $id): ArticleResource
    {
        $article = $this->service->findById($id);

        if (! $article) {
            abort(404, 'Artigo não encontrado.');
        }

        $this->authorize('view', $article);

        return new ArticleResource($article);
    }

    public function update(UpdateArticleRequest $request, int $id): ArticleResource
    {
        $article = $this->service->findById($id);

        if (! $article) {
            abort(404, 'Artigo não encontrado.');
        }

        $this->authorize('update', $article);

        $dto = UpdateArticleDTO::fromArray($request->validated());
        $updatedArticle = $this->service->update($article, $dto);

        return new ArticleResource($updatedArticle);
    }

    public function archive(int $id): ArticleResource
    {
        $article = $this->service->findById($id);

        if (! $article) {
            abort(404, 'Artigo não encontrado.');
        }

        $this->authorize('archive', $article);

        $archivedArticle = $this->service->archive($article);

        return new ArticleResource($archivedArticle);
    }

    public function destroy(int $id): Response
    {
        $article = $this->service->findById($id);

        if (! $article) {
            abort(404, 'Artigo não encontrado.');
        }

        $this->authorize('delete', $article);

        $this->service->delete($article);

        return response()->noContent();
    }
}
