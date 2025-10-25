<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;

class PreferenceService
{
    /**
     * Get user preferences
     *
     * @param User $user
     * @return array
     */
    public function getUserPreferences(User $user): array
    {
        $preference = $user->preference;

        if (!$preference) {
            return [
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
                'keywords' => [],
            ];
        }

        return [
            'preferred_sources' => $preference->preferred_sources ?? [],
            'preferred_categories' => $preference->preferred_categories ?? [],
            'preferred_authors' => $preference->preferred_authors ?? [],
            'keywords' => $preference->keywords ?? [],
        ];
    }

    /**
     * Store or update user preferences
     *
     * @param User $user
     * @param array $data
     * @return UserPreference
     */
    public function saveUserPreferences(User $user, array $data): UserPreference
    {
        return UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }

    /**
     * Delete user preferences
     *
     * @param User $user
     * @return bool
     */
    public function deleteUserPreferences(User $user): bool
    {
        $preference = $user->preference;

        if (!$preference) {
            return false;
        }

        return $preference->delete();
    }
}
