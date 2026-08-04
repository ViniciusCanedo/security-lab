<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ArticleQueryDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\Request;

class PublicArticleController extends Controller
{
    public function __construct(
        protected ArticleService $service
    ) {}

    public function index(Request $request): ArticleCollection
    {
        $queryDto = ArticleQueryDTO::fromArray($request->all());
        $articles = $this->service->getPublishedPaginated($queryDto);

        return new ArticleCollection($articles);
    }

    public function show(int|string $idOrSlug): ArticleResource
    {
        $article = is_numeric($idOrSlug)
            ? $this->service->findPublishedById((int) $idOrSlug)
            : $this->service->findPublishedBySlug($idOrSlug);

        if (! $article) {
            abort(404, 'Artigo não encontrado.');
        }

        return new ArticleResource($article);
    }
}
