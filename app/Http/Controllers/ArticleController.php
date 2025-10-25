<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ArticleFilterData;
use App\Enums\NewsSource;
use App\Http\Requests\ArticleIndexRequest;
use App\Http\Resources\ArticleCollection;
use App\Http\Resources\ArticleResource;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService
    ) {}

    /**
     * Display a listing of articles with filters and pagination
     *
     * @param ArticleIndexRequest $request
     * @return ArticleCollection
     */
    public function index(ArticleIndexRequest $request): ArticleCollection
    {
        $filters = ArticleFilterData::fromRequest($request->validated());

        $articles = $this->articleService->getPaginatedArticles($filters, $request->user());

        return new ArticleCollection($articles);
    }

    /**
     * Display the specified article
     *
     * @param int $id
     * @return ArticleResource|JsonResponse
     */
    public function show(int $id): ArticleResource|JsonResponse
    {
        $article = $this->articleService->findArticleById($id);

        if (!$article) {
            return response()->json([
                'message' => 'Article not found'
            ], 404);
        }

        return new ArticleResource($article);
    }

    /**
     * Get available news sources
     *
     * @return JsonResponse
     */
    public function sources(): JsonResponse
    {
        $sources = collect(NewsSource::cases())->map(fn($source) => [
            'id' => $source->value,
            'name' => $source->label(),
            'enabled' => $source->isEnabled(),
        ]);

        return response()->json([
            'data' => $sources
        ]);
    }

    /**
     * Get available categories
     *
     * @return JsonResponse
     */
    public function categories(): JsonResponse
    {
        $categories = $this->articleService->getAvailableCategories();

        return response()->json([
            'data' => $categories
        ]);
    }

    /**
     * Get available authors
     *
     * @return JsonResponse
     */
    public function authors(): JsonResponse
    {
        $authors = $this->articleService->getAvailableAuthors();

        return response()->json([
            'data' => $authors
        ]);
    }
}

