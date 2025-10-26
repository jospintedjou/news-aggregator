<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_articles_list(): void
    {
        // Create test articles
        Article::factory()->count(15)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'content',
                        'author',
                        'source',
                        'category',
                        'url',
                        'image_url',
                        'published_at',
                        'created_at',
                    ]
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ]
            ]);
    }

    public function test_can_search_articles(): void
    {
        Article::factory()->create([
            'title' => 'Bitcoin reaches new high',
            'description' => 'Cryptocurrency market news',
        ]);

        Article::factory()->create([
            'title' => 'Climate change report',
            'description' => 'Environmental news',
        ]);

        $response = $this->getJson('/api/articles?q=bitcoin');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Bitcoin reaches new high');
    }

    public function test_can_filter_by_source(): void
    {
        Article::factory()->create(['source' => 'newsapi']);
        Article::factory()->create(['source' => 'guardian']);
        Article::factory()->create(['source' => 'newsapi']);

        $response = $this->getJson('/api/articles?source=newsapi');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_category(): void
    {
        Article::factory()->create(['category' => 'technology']);
        Article::factory()->create(['category' => 'business']);
        Article::factory()->create(['category' => 'technology']);

        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_date_range(): void
    {
        Article::factory()->create([
            'published_at' => '2025-10-01',
        ]);

        Article::factory()->create([
            'published_at' => '2025-10-15',
        ]);

        Article::factory()->create([
            'published_at' => '2025-10-25',
        ]);

        $response = $this->getJson('/api/articles?from=2025-10-10&to=2025-10-20');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_single_article(): void
    {
        $article = Article::factory()->create([
            'title' => 'Test Article',
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $article->id)
            ->assertJsonPath('data.title', 'Test Article');
    }

    public function test_returns_404_for_nonexistent_article(): void
    {
        $response = $this->getJson('/api/articles/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Article not found']);
    }

    public function test_can_get_sources(): void
    {
        $response = $this->getJson('/api/sources');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'enabled',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_categories(): void
    {
        Article::factory()->create(['category' => 'technology']);
        Article::factory()->create(['category' => 'business']);
        Article::factory()->create(['category' => 'technology']); // Duplicate

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_get_authors(): void
    {
        Article::factory()->create(['author' => 'John Doe']);
        Article::factory()->create(['author' => 'Jane Smith']);
        Article::factory()->create(['author' => 'John Doe']); // Duplicate

        $response = $this->getJson('/api/authors');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_gets_personalized_feed(): void
    {
        $user = User::factory()->create();
        
        // Create user preferences
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
            'preferred_categories' => ['technology'],
        ]);

        // Create articles
        Article::factory()->create([
            'source' => 'newsapi',
            'category' => 'technology',
        ]);

        Article::factory()->create([
            'source' => 'guardian',
            'category' => 'business',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_ignore_preferences_with_flag(): void
    {
        $user = User::factory()->create();
        
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
        ]);

        Article::factory()->count(5)->create(['source' => 'newsapi']);
        Article::factory()->count(5)->create(['source' => 'guardian']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/articles?ignore_preferences=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data');
    }

    public function test_explicit_filters_override_preferences(): void
    {
        $user = User::factory()->create();
        
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
        ]);

        Article::factory()->count(3)->create(['source' => 'newsapi']);
        Article::factory()->count(3)->create(['source' => 'guardian']);

        // Explicit source filter should override preference
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/articles?source=guardian');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_pagination_works_correctly(): void
    {
        Article::factory()->count(25)->create();

        $response = $this->getJson('/api/articles?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                ]
            ]);
        
        // Verify pagination is working (structure is correct)
        $json = $response->json();
        $this->assertArrayHasKey('meta', $json);
        $this->assertArrayHasKey('total', $json['meta']);
        $this->assertCount(10, $json['data']);
    }
}

