<?php

namespace App\Repositories;

use App\DataTransferObjects\ArticleData;
use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ArticleRepository
{
    /**
     * Get paginated articles with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::query();

        $this->applyFilters($query, $filters);

        return $query->recent()->paginate($perPage);
    }

    /**
     * Get articles for a specific user with their preferences applied
     *
     * @param User $user
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::query();

        $hasExplicitFilters = $this->applyFilters($query, $filters);

        if (!$hasExplicitFilters && !($filters['ignore_preferences'] ?? false)) {
            $this->applyUserPreferences($query, $user);
        }

        return $query->recent()->paginate($perPage);
    }

    /**
     * Apply filters to query
     *
     * @param Builder $query
     * @param array $filters
     * @return bool Whether any filters were applied
     */
    protected function applyFilters(Builder $query, array $filters): bool
    {
        $filtersApplied = false;

        if (!empty($filters['q'])) {
            $query->search($filters['q']);
            $filtersApplied = true;
        }

        if (!empty($filters['source'])) {
            $sources = is_array($filters['source']) ? $filters['source'] : explode(',', $filters['source']);
            $query->bySource($sources);
            $filtersApplied = true;
        }

        if (!empty($filters['category'])) {
            $categories = is_array($filters['category']) ? $filters['category'] : explode(',', $filters['category']);
            $query->byCategory($categories);
            $filtersApplied = true;
        }

        if (!empty($filters['author'])) {
            $authors = is_array($filters['author']) ? $filters['author'] : explode(',', $filters['author']);
            $query->byAuthor($authors);
            $filtersApplied = true;
        }

        if (!empty($filters['from']) || !empty($filters['to'])) {
            $query->byDateRange($filters['from'] ?? null, $filters['to'] ?? null);
            $filtersApplied = true;
        }

        return $filtersApplied;
    }

    /**
     * Apply user preferences to query
     *
     * @param Builder $query
     * @param User $user
     * @return void
     */
    protected function applyUserPreferences(Builder $query, User $user): void
    {
        $preferences = $user->preference;

        if (!$preferences || !$preferences->hasPreferences()) {
            return;
        }

        if (!empty($preferences->preferred_sources)) {
            $query->bySource($preferences->preferred_sources);
        }

        if (!empty($preferences->preferred_categories)) {
            $query->byCategory($preferences->preferred_categories);
        }

        if (!empty($preferences->preferred_authors)) {
            $query->byAuthor($preferences->preferred_authors);
        }

        if (!empty($preferences->keywords)) {
            $query->where(function ($q) use ($preferences) {
                foreach ($preferences->keywords as $keyword) {
                    $q->orWhere('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%")
                      ->orWhere('content', 'like', "%{$keyword}%");
                }
            });
        }
    }

    /**
     * Find article by ID
     *
     * @param int $id
     * @return Article|null
     */
    public function findById(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * Bulk upsert articles from ArticleData objects
     *
     * @param array<ArticleData> $articles
     * @return int Number of articles inserted/updated
     */
    public function bulkUpsert(array $articles): int
    {
        if (empty($articles)) {
            return 0;
        }

        $data = array_map(fn(ArticleData $article) => $article->toArray(), $articles);

        // Use upsert to insert new or update existing based on unique key (source, external_id)
        return DB::table('articles')->upsert(
            $data,
            ['source', 'external_id'],
            ['title', 'description', 'content', 'author', 'category', 'url', 'image_url', 'published_at', 'updated_at']
        );
    }

    /**
     * Get distinct categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return Article::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
     * Get distinct authors
     *
     * @return array
     */
    public function getAuthors(): array
    {
        return Article::whereNotNull('author')
            ->distinct()
            ->pluck('author')
            ->toArray();
    }

    /**
     * Get article count by source
     *
     * @return array
     */
    public function getCountBySource(): array
    {
        return Article::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();
    }

    /**
     * Delete old articles
     *
     * @param int $daysOld
     * @return int Number of articles deleted
     */
    public function deleteOlderThan(int $daysOld): int
    {
        return Article::where('published_at', '<', now()->subDays($daysOld))->delete();
    }

    /**
     * Check if article exists by source and external ID
     *
     * @param string $source
     * @param string $externalId
     * @return bool
     */
    public function existsBySourceAndExternalId(string $source, string $externalId): bool
    {
        return Article::where('source', $source)
            ->where('external_id', $externalId)
            ->exists();
    }
}
