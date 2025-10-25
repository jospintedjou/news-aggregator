<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreferenceRequest;
use App\Services\PreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function __construct(
        protected PreferenceService $preferenceService
    ) {}

    /**
     * Get the authenticated user's preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $preferences = $this->preferenceService->getUserPreferences($request->user());

        return response()->json([
            'data' => $preferences
        ]);
    }

    /**
     * Store or update user preferences
     *
     * @param StorePreferenceRequest $request
     * @return JsonResponse
     */
    public function store(StorePreferenceRequest $request): JsonResponse
    {
        $preference = $this->preferenceService->saveUserPreferences(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Preferences saved successfully',
            'data' => [
                'preferred_sources' => $preference->preferred_sources ?? [],
                'preferred_categories' => $preference->preferred_categories ?? [],
                'preferred_authors' => $preference->preferred_authors ?? [],
                'keywords' => $preference->keywords ?? [],
            ]
        ]);
    }

    /**
     * Delete user preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->preferenceService->deleteUserPreferences($request->user());

        return response()->json([
            'message' => 'Preferences deleted successfully'
        ]);
    }
}

