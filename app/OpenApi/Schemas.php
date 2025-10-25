<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     title="Article",
 *     description="News article object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="source", type="string", enum={"newsapi", "guardian", "nytimes"}, example="newsapi"),
 *     @OA\Property(property="author", type="string", nullable=true, example="John Smith"),
 *     @OA\Property(property="title", type="string", example="Breaking News: Technology Advances"),
 *     @OA\Property(property="description", type="string", nullable=true, example="A detailed look at recent technology developments"),
 *     @OA\Property(property="url", type="string", format="uri", example="https://example.com/article"),
 *     @OA\Property(property="image_url", type="string", format="uri", nullable=true, example="https://example.com/image.jpg"),
 *     @OA\Property(property="published_at", type="string", format="date-time", example="2025-10-25T10:30:00Z"),
 *     @OA\Property(property="content", type="string", nullable=true, example="Full article content goes here..."),
 *     @OA\Property(property="category", type="string", nullable=true, example="technology"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-25T10:35:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-25T10:35:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="UserPreference",
 *     type="object",
 *     title="User Preference",
 *     description="User news preference settings",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(
 *         property="preferred_sources",
 *         type="array",
 *         @OA\Items(type="string", enum={"newsapi", "guardian", "nytimes"}),
 *         example={"newsapi", "guardian"}
 *     ),
 *     @OA\Property(
 *         property="preferred_categories",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"technology", "business"}
 *     ),
 *     @OA\Property(
 *         property="preferred_authors",
 *         type="array",
 *         @OA\Items(type="string"),
 *         example={"John Smith", "Jane Doe"}
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User account object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Schemas
{
}
