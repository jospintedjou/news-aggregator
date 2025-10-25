<?php

namespace App\OpenApi;

class ArticleAnnotations
{
    /**
     * @OA\Get(
     *     path="/articles",
     *     tags={"Articles"},
     *     summary="Get all articles with filtering",
     *     description="Retrieve a paginated list of articles with optional filtering by keyword, source, category, date range, and author",
     *     operationId="getArticles",
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search keyword (searches in title, description, content)",
     *         required=false,
     *         @OA\Schema(type="string", example="technology")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter by news source",
     *         required=false,
     *         @OA\Schema(type="string", enum={"newsapi", "guardian", "nytimes"}, example="newsapi")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string", example="business")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter articles from this date (inclusive)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter articles up to this date (inclusive)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filter by author name",
     *         required=false,
     *         @OA\Schema(type="string", example="John Smith")
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

    /**
     * @OA\Get(
     *     path="/articles/personalized",
     *     tags={"Articles"},
     *     summary="Get personalized articles",
     *     description="Retrieve articles filtered by user preferences (requires authentication)",
     *     operationId="getPersonalizedArticles",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
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
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getPersonalizedArticles() {}
}
