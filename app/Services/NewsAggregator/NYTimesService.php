<?php

namespace App\Services\NewsAggregator;

use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use Carbon\Carbon;

class NYTimesService extends BaseNewsService
{
    /**
     * Get the news source identifier
     */
    public function getSource(): NewsSource
    {
        return NewsSource::NYTIMES;
    }

    /**
     * Fetch articles from New York Times API
     *
     * @param array $params
     * @return array<ArticleData>
     */
    public function fetchArticles(array $params = []): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        // Use Article Search API
        $endpoint = $this->config['endpoints']['article_search'] ?? '/search/v2/articlesearch.json';
        
        $queryParams = [
            'api-key' => $this->apiKey,
            'page' => $params['page'] ?? 0,
            'sort' => $params['sort'] ?? 'newest',
        ];

        // Add optional parameters
        if (!empty($params['q'])) {
            $queryParams['q'] = $params['q'];
        }

        if (!empty($params['fq'])) {
            $queryParams['fq'] = $params['fq'];
        }

        if (!empty($params['begin_date'])) {
            $queryParams['begin_date'] = $params['begin_date'];
        }

        if (!empty($params['end_date'])) {
            $queryParams['end_date'] = $params['end_date'];
        }

        $response = $this->makeRequest($endpoint, $queryParams);

        if (empty($response['response']['docs'])) {
            return [];
        }

        return array_map(
            fn($article) => $this->transformArticle($article), 
            $response['response']['docs']
        );
    }

    /**
     * Transform NYTimes article to ArticleData object
     *
     * @param array $rawArticle
     * @return ArticleData
     */
    public function transformArticle(array $rawArticle): ArticleData
    {
        // Get the best available image
        $imageUrl = null;
        if (!empty($rawArticle['multimedia'])) {
            foreach ($rawArticle['multimedia'] as $media) {
                if (isset($media['url'])) {
                    $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                    break;
                }
            }
        }

        // Get author name
        $author = null;
        if (!empty($rawArticle['byline']['original'])) {
            $author = $rawArticle['byline']['original'];
        }

        // Get section/category
        $category = $rawArticle['section_name'] ?? $rawArticle['news_desk'] ?? null;

        return $this->createArticleData([
            'title' => $rawArticle['headline']['main'] ?? '',
            'description' => $rawArticle['abstract'] ?? null,
            'content' => $rawArticle['lead_paragraph'] ?? null,
            'author' => $author,
            'category' => $category,
            'url' => $rawArticle['web_url'] ?? '',
            'image_url' => $imageUrl,
            'external_id' => $rawArticle['_id'] ?? '',
            'published_at' => isset($rawArticle['pub_date']) 
                ? Carbon::parse($rawArticle['pub_date']) 
                : now(),
        ]);
    }
}
