<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="News Aggregator API",
 *     description="A comprehensive news aggregator API that fetches articles from multiple sources (NewsAPI, The Guardian, New York Times) and provides personalized news feeds based on user preferences.",
 *     @OA\Contact(
 *         name="API Support",
 *         email="support@newsaggregator.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.newsaggregator.com/api",
 *     description="Production server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints (register, login, logout)"
 * )
 * 
 * @OA\Tag(
 *     name="Articles",
 *     description="Article retrieval and filtering endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Metadata",
 *     description="Endpoints for sources, categories, and authors"
 * )
 * 
 * @OA\Tag(
 *     name="Preferences",
 *     description="User preference management (requires authentication)"
 * )
 */
class OpenApiInfo
{
}
