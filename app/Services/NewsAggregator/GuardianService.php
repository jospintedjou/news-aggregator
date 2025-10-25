<?php

namespace App\Services\NewsAggregator;

use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use Carbon\Carbon;

class GuardianService extends BaseNewsService
{
    /**
     * Get the news source identifier
     */
    public function getSource(): NewsSource
    {
        return NewsSource::GUARDIAN;
    }

    /**
     * Fetch articles from The Guardian API
     *
     * @param array $params
     * @return array<ArticleData>
     */
    public function fetchArticles(array $params = []): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $endpoint = $this->config['endpoints']['search'] ?? '/search';
        
        $queryParams = [
            'api-key' => $this->apiKey,
            'page-size' => $params['pageSize'] ?? 100,
            'page' => $params['page'] ?? 1,
            'order-by' => $params['orderBy'] ?? 'newest',
            'show-fields' => 'trailText,body,byline,thumbnail',
        ];

        if (!empty($params['section'])) {
            $queryParams['section'] = $params['section'];
        }

        if (!empty($params['q'])) {
            $queryParams['q'] = $params['q'];
        }

        if (!empty($params['from-date'])) {
            $queryParams['from-date'] = $params['from-date'];
        }

        $response = $this->makeRequest($endpoint, $queryParams);

        if (empty($response['response']['results'])) {
            return [];
        }

        return array_map(
            fn($article) => $this->transformArticle($article), 
            $response['response']['results']
        );
    }

    /**
     * Transform Guardian article to ArticleData object
     *
     * @param array $rawArticle
     * @return ArticleData
     */
    public function transformArticle(array $rawArticle): ArticleData
    {
        $fields = $rawArticle['fields'] ?? [];

        return $this->createArticleData([
            'title' => $rawArticle['webTitle'] ?? '',
            'description' => $fields['trailText'] ?? null,
            'content' => $fields['body'] ?? null,
            'author' => $fields['byline'] ?? null,
            'category' => $rawArticle['sectionName'] ?? null,
            'url' => $rawArticle['webUrl'] ?? '',
            'image_url' => $fields['thumbnail'] ?? null,
            'external_id' => $rawArticle['id'] ?? '',
            'published_at' => isset($rawArticle['webPublicationDate']) 
                ? Carbon::parse($rawArticle['webPublicationDate']) 
                : now(),
        ]);
    }
}
