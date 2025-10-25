<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\ArticleRepository;
use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository();
    }

    public function test_get_paginated_returns_articles(): void
    {
        Article::factory()->count(15)->create();

        $result = $this->repository->getPaginated([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_apply_filters_by_source(): void
    {
        Article::factory()->newsapi()->count(5)->create();
        Article::factory()->guardian()->count(3)->create();

        $result = $this->repository->getPaginated(['source' => 'newsapi']);

        $this->assertEquals(5, $result->total());
        foreach ($result->items() as $article) {
            $this->assertEquals(NewsSource::NEWSAPI, $article->source);
        }
    }

    public function test_apply_filters_by_category(): void
    {
        Article::factory()->category('technology')->count(7)->create();
        Article::factory()->category('business')->count(3)->create();

        $result = $this->repository->getPaginated(['category' => 'technology']);

        $this->assertEquals(7, $result->total());
    }

    public function test_apply_filters_by_author(): void
    {
        Article::factory()->byAuthor('John Doe')->count(4)->create();
        Article::factory()->byAuthor('Jane Smith')->count(6)->create();

        $result = $this->repository->getPaginated(['author' => 'John Doe']);

        $this->assertEquals(4, $result->total());
    }

    public function test_apply_filters_by_date_range(): void
    {
        Article::factory()->publishedAt(now()->subDays(5))->count(3)->create();
        Article::factory()->publishedAt(now()->subDays(15))->count(2)->create();
        Article::factory()->publishedAt(now()->subDays(25))->count(4)->create();

        $result = $this->repository->getPaginated([
            'from' => now()->subDays(10)->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(3, $result->total());
    }

    public function test_search_filters_articles(): void
    {
        Article::factory()->create(['title' => 'Laravel Framework News']);
        Article::factory()->create(['title' => 'React Component Updates']);
        Article::factory()->create(['description' => 'Article about Laravel testing']);

        $result = $this->repository->getPaginated(['q' => 'Laravel']);

        $this->assertEquals(2, $result->total());
    }

    public function test_apply_user_preferences_sources(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi', 'guardian'],
        ]);

        Article::factory()->newsapi()->count(5)->create();
        Article::factory()->guardian()->count(3)->create();
        Article::factory()->nytimes()->count(2)->create();

        $result = $this->repository->getPaginatedForUser($user, []);

        $this->assertEquals(8, $result->total());
    }

    public function test_apply_user_preferences_categories(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_categories' => ['technology', 'business'],
        ]);

        Article::factory()->category('technology')->count(4)->create();
        Article::factory()->category('business')->count(3)->create();
        Article::factory()->category('sports')->count(5)->create();

        $result = $this->repository->getPaginatedForUser($user, []);

        $this->assertEquals(7, $result->total());
    }

    public function test_apply_user_preferences_authors(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_authors' => ['John Doe', 'Jane Smith'],
        ]);

        Article::factory()->byAuthor('John Doe')->count(3)->create();
        Article::factory()->byAuthor('Jane Smith')->count(2)->create();
        Article::factory()->byAuthor('Bob Wilson')->count(4)->create();

        $result = $this->repository->getPaginatedForUser($user, []);

        $this->assertEquals(5, $result->total());
    }

    public function test_explicit_filters_override_preferences(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
        ]);

        Article::factory()->newsapi()->count(5)->create();
        Article::factory()->guardian()->count(3)->create();

        // Explicitly filter by guardian should override preference
        $result = $this->repository->getPaginatedForUser($user, ['source' => 'guardian']);

        $this->assertEquals(3, $result->total());
    }

    public function test_ignore_preferences_flag(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'preferred_sources' => ['newsapi'],
        ]);

        Article::factory()->newsapi()->count(5)->create();
        Article::factory()->guardian()->count(3)->create();

        $result = $this->repository->getPaginatedForUser($user, ['ignore_preferences' => true]);

        $this->assertEquals(8, $result->total());
    }

    public function test_bulk_upsert_creates_new_articles(): void
    {
        $articlesData = [
            new ArticleData(
                title: 'Test Article 1',
                description: 'Description 1',
                content: 'Content 1',
                author: 'Author 1',
                source: NewsSource::NEWSAPI->value,
                category: 'technology',
                url: 'https://example.com/1',
                imageUrl: 'https://example.com/image1.jpg',
                externalId: 'ext-1',
                publishedAt: now()
            ),
            new ArticleData(
                title: 'Test Article 2',
                description: 'Description 2',
                content: 'Content 2',
                author: 'Author 2',
                source: NewsSource::GUARDIAN->value,
                category: 'business',
                url: 'https://example.com/2',
                imageUrl: 'https://example.com/image2.jpg',
                externalId: 'ext-2',
                publishedAt: now()
            ),
        ];

        $count = $this->repository->bulkUpsert($articlesData);

        $this->assertEquals(2, $count);
        $this->assertDatabaseHas('articles', ['external_id' => 'ext-1']);
        $this->assertDatabaseHas('articles', ['external_id' => 'ext-2']);
    }

    public function test_bulk_upsert_updates_existing_articles(): void
    {
        Article::create([
            'title' => 'Old Title',
            'description' => 'Old Description',
            'content' => 'Old Content',
            'author' => 'Old Author',
            'source' => NewsSource::NEWSAPI,
            'category' => 'technology',
            'url' => 'https://example.com/1',
            'image_url' => 'https://example.com/old.jpg',
            'external_id' => 'ext-1',
            'published_at' => now()->subDay(),
        ]);

        $articlesData = [
            new ArticleData(
                title: 'Updated Title',
                description: 'Updated Description',
                content: 'Updated Content',
                author: 'Updated Author',
                source: NewsSource::NEWSAPI->value,
                category: 'business',
                url: 'https://example.com/1',
                imageUrl: 'https://example.com/new.jpg',
                externalId: 'ext-1',
                publishedAt: now()
            ),
        ];

        $count = $this->repository->bulkUpsert($articlesData);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('articles', [
            'external_id' => 'ext-1',
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ]);
        $this->assertEquals(1, Article::count());
    }

    public function test_get_categories_returns_distinct_categories(): void
    {
        Article::factory()->category('technology')->count(5)->create();
        Article::factory()->category('business')->count(3)->create();
        Article::factory()->category('sports')->count(2)->create();

        $categories = $this->repository->getCategories();

        $this->assertCount(3, $categories);
        $this->assertContains('technology', $categories);
        $this->assertContains('business', $categories);
        $this->assertContains('sports', $categories);
    }

    public function test_get_authors_returns_distinct_authors(): void
    {
        Article::factory()->byAuthor('John Doe')->count(5)->create();
        Article::factory()->byAuthor('Jane Smith')->count(3)->create();
        Article::factory()->byAuthor('Bob Wilson')->count(2)->create();

        $authors = $this->repository->getAuthors();

        $this->assertCount(3, $authors);
        $this->assertContains('John Doe', $authors);
        $this->assertContains('Jane Smith', $authors);
        $this->assertContains('Bob Wilson', $authors);
    }

    public function test_delete_older_than_removes_old_articles(): void
    {
        Article::factory()->publishedAt(now()->subDays(40))->count(5)->create();
        Article::factory()->publishedAt(now()->subDays(20))->count(3)->create();
        Article::factory()->publishedAt(now()->subDays(5))->count(2)->create();

        $count = $this->repository->deleteOlderThan(30);

        $this->assertEquals(5, $count);
        $this->assertEquals(5, Article::count());
    }

    public function test_delete_older_than_keeps_recent_articles(): void
    {
        Article::factory()->publishedAt(now()->subDays(20))->count(5)->create();

        $count = $this->repository->deleteOlderThan(30);

        $this->assertEquals(0, $count);
        $this->assertEquals(5, Article::count());
    }
}

