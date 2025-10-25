<?php

namespace App\Models;

use App\Enums\NewsSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'content',
        'author',
        'source',
        'category',
        'url',
        'image_url',
        'external_id',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'source' => NewsSource::class,
        ];
    }

    /**
     * Scope a query to filter by source(s)
     */
    public function scopeBySource(Builder $query, string|array $sources): Builder
    {
        $sources = is_array($sources) ? $sources : [$sources];
        return $query->whereIn('source', $sources);
    }

    /**
     * Scope a query to filter by category/categories
     */
    public function scopeByCategory(Builder $query, string|array $categories): Builder
    {
        $categories = is_array($categories) ? $categories : [$categories];
        return $query->whereIn('category', $categories);
    }

    /**
     * Scope a query to filter by author(s)
     */
    public function scopeByAuthor(Builder $query, string|array $authors): Builder
    {
        $authors = is_array($authors) ? $authors : [$authors];
        return $query->whereIn('author', $authors);
    }

    /**
     * Scope a query to search in title, description, and content
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeByDateRange(Builder $query, ?string $from = null, ?string $to = null): Builder
    {
        if ($from) {
            $query->where('published_at', '>=', $from);
        }
        if ($to) {
            $query->where('published_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope a query to get recent articles
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('published_at', 'desc');
    }
}
