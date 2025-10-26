<?php

namespace App\OpenApi;

class ArticleAnnotations
{
    /**
     * @OA\Get(
     *     path="/articles",
     *     tags={"Articles"},
     *     summary="Get all articles with filtering",
     *     description="Retrieve a paginated list of articles with optional filtering. When authenticated, user preferences (preferred sources, categories, keywords) are automatically applied unless 'ignore_preferences=1' is set. Explicit filters override preferences.",
     *     operationId="getArticles",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search keyword (searches in title, description, content)",
     *         required=false,
     *         @OA\Schema(type="string", example="news")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter by news source (comma-separated for multiple sources). When authenticated, this overrides user's preferred sources.",
     *         required=false,
     *         @OA\Schema(type="string", example="newsapi,guardian")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category. When authenticated, this overrides user's preferred categories.",
     *         required=false,
     *         @OA\Schema(type="string", example="business")
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filter by author name. When authenticated, this overrides user's preferred authors.",
     *         required=false,
     *         @OA\Schema(type="string", example="John Smith")
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Filter articles from this date (inclusive)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="Filter articles up to this date (inclusive)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, example=15)
     *     ),
     *     @OA\Parameter(
     *         name="ignore_preferences",
     *         in="query",
     *         description="Set to 1 to ignore user preferences and return all articles (authenticated users only)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Article")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=72)
     *             )
     *         )
     *     )
     * )
     */
    public function getArticles() {}

    /**
     * @OA\Get(
     *     path="/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get a single article",
     *     description="Retrieve detailed information about a specific article",
     *     operationId="getArticle",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Article ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Article")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article not found")
     *         )
     *     )
     * )
     */
    public function getArticle() {}
}
