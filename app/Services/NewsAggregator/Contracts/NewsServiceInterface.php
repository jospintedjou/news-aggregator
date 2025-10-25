<?php

namespace App\Services\NewsAggregator\Contracts;

use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;

interface NewsServiceInterface
{
    /**
     * Fetch articles from the news source
     *
     * @param array $params Query parameters (category, search keyword, page, etc.)
     * @return array<ArticleData> Array of ArticleData objects
     */
    public function fetchArticles(array $params = []): array;

    /**
     * Get the news source identifier
     *
     * @return NewsSource
     */
    public function getSource(): NewsSource;

    /**
     * Check if the service is properly configured
     *
     * @return bool
     */
    public function isConfigured(): bool;

    /**
     * Transform API response to standardized ArticleData object
     *
     * @param array $rawArticle
     * @return ArticleData
     */
    public function transformArticle(array $rawArticle): ArticleData;
}
