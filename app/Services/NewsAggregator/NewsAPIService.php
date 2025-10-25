<?php

namespace App\Services\NewsAggregator;

use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use Carbon\Carbon;

class NewsAPIService extends BaseNewsService
{
    /**
     * Get the news source identifier
     */
    public function getSource(): NewsSource
    {
        return NewsSource::NEWSAPI;
    }

    /**
     * Fetch articles from NewsAPI
     *
     * @param array $params
     * @return array<ArticleData>
     */
    public function fetchArticles(array $params = []): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $endpoint = $this->config['endpoints']['everything'] ?? '/everything';
        
        $queryParams = [
            'apiKey' => $this->apiKey,
            'language' => $params['language'] ?? 'en',
            'pageSize' => $params['pageSize'] ?? 100,
            'page' => $params['page'] ?? 1,
            'sortBy' => $params['sortBy'] ?? 'publishedAt',
            // NewsAPI requires at least one of: q, qInTitle, sources, or domains
            // Default to fetching general news if no specific query is provided
            'q' => $params['q'] ?? 'news',
        ];

        if (!empty($params['category'])) {
            $queryParams['category'] = $params['category'];
        }

        if (!empty($params['from'])) {
            $queryParams['from'] = $params['from'];
        }

        $response = $this->makeRequest($endpoint, $queryParams);

        if (empty($response['articles'])) {
            return [];
        }

        return array_map(fn($article) => $this->transformArticle($article), $response['articles']);
    }

    /**
     * Transform NewsAPI article to ArticleData object
     *
     * @param array $rawArticle
     * @return ArticleData
     */
    public function transformArticle(array $rawArticle): ArticleData
    {
        return $this->createArticleData([
            'title' => $rawArticle['title'] ?? '',
            'description' => $rawArticle['description'] ?? null,
            'content' => $rawArticle['content'] ?? null,
            'author' => $rawArticle['author'] ?? null,
            'category' => null, // NewsAPI doesn't provide category in response
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $rawArticle['urlToImage'] ?? null,
            'external_id' => md5($rawArticle['url'] ?? ''),
            'published_at' => isset($rawArticle['publishedAt']) 
                ? Carbon::parse($rawArticle['publishedAt']) 
                : now(),
        ]);
    }
}
