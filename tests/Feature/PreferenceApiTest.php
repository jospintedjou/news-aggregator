<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_preferences(): void
    {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(401);
    }

    public function test_can_get_user_preferences(): void
    {
        $user = User::factory()->create();
        
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi', 'guardian'],
            'preferred_categories' => ['technology', 'business'],
            'preferred_authors' => ['John Doe'],
            'keywords' => ['AI', 'crypto'],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'preferred_sources' => ['newsapi', 'guardian'],
                    'preferred_categories' => ['technology', 'business'],
                    'preferred_authors' => ['John Doe'],
                    'keywords' => ['AI', 'crypto'],
                ]
            ]);
    }

    public function test_returns_empty_preferences_if_none_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'preferred_sources' => [],
                    'preferred_categories' => [],
                    'preferred_authors' => [],
                    'keywords' => [],
                ]
            ]);
    }

    public function test_can_save_preferences(): void
    {
        $user = User::factory()->create();

        $preferences = [
            'preferred_sources' => ['newsapi', 'guardian'],
            'preferred_categories' => ['technology'],
            'preferred_authors' => ['Jane Smith'],
            'keywords' => ['startup', 'innovation'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/preferences', $preferences);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferences saved successfully',
                'data' => $preferences
            ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_can_update_existing_preferences(): void
    {
        $user = User::factory()->create();
        
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['business'],
        ]);

        $newPreferences = [
            'preferred_sources' => ['guardian', 'nytimes'],
            'preferred_categories' => ['technology', 'sports'],
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/preferences', $newPreferences);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
        ]);

        $preference = $user->fresh()->preference;
        $this->assertEquals(['guardian', 'nytimes'], $preference->preferred_sources);
        $this->assertEquals(['technology', 'sports'], $preference->preferred_categories);
    }

    public function test_validates_preferred_sources(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/preferences', [
                'preferred_sources' => ['invalid_source'],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preferred_sources.0']);
    }

    public function test_validates_array_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/preferences', [
                'preferred_sources' => 'not_an_array',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preferred_sources']);
    }

    public function test_can_save_partial_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/preferences', [
                'preferred_sources' => ['newsapi'],
                // Other fields are optional
            ]);

        $response->assertStatus(200);
    }

    public function test_can_delete_preferences(): void
    {
        $user = User::factory()->create();
        
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Preferences deleted successfully'
            ]);

        $this->assertDatabaseMissing('user_preferences', [
            'user_id' => $user->id,
        ]);
    }

    public function test_delete_preferences_works_even_if_none_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/preferences');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_save_preferences(): void
    {
        $response = $this->postJson('/api/preferences', [
            'preferred_sources' => ['newsapi'],
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_preferences(): void
    {
        $response = $this->deleteJson('/api/preferences');

        $response->assertStatus(401);
    }
}

