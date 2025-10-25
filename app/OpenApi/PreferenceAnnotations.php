<?php

namespace App\OpenApi;

class PreferenceAnnotations
{
    /**
     * @OA\Get(
     *     path="/preferences",
     *     tags={"Preferences"},
     *     summary="Get user preferences",
     *     description="Retrieve the authenticated user's news preferences",
     *     operationId="getPreferences",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="preferred_sources",
     *                     type="array",
     *                     @OA\Items(type="string", example="newsapi")
     *                 ),
     *                 @OA\Property(
     *                     property="preferred_categories",
     *                     type="array",
     *                     @OA\Items(type="string", example="technology")
     *                 ),
     *                 @OA\Property(
     *                     property="preferred_authors",
     *                     type="array",
     *                     @OA\Items(type="string", example="John Smith")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preferences not found"
     *     )
     * )
     */
    public function getPreferences() {}

    /**
     * @OA\Post(
     *     path="/preferences",
     *     tags={"Preferences"},
     *     summary="Create or update user preferences",
     *     description="Set the authenticated user's news preferences",
     *     operationId="updatePreferences",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="preferred_sources",
     *                 type="array",
     *                 @OA\Items(type="string", enum={"newsapi", "guardian", "nytimes"}),
     *                 example={"newsapi", "guardian"}
     *             ),
     *             @OA\Property(
     *                 property="preferred_categories",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"technology", "business"}
     *             ),
     *             @OA\Property(
     *                 property="preferred_authors",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"John Smith", "Jane Doe"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferences updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Preferences updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserPreference")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updatePreferences() {}
}
