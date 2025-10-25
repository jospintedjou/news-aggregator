<?php

namespace App\Services\NewsAggregator;

use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use App\Services\NewsAggregator\Contracts\NewsServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseNewsService implements NewsServiceInterface
{
    protected array $config;
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->config = $this->getSource()->config();
        $this->baseUrl = $this->config['base_url'] ?? '';
        $this->apiKey = $this->config['api_key'] ?? '';
    }

    /**
     * Make HTTP request to the news API
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::timeout(config('news-sources.timeout', 30))
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("News API request failed for {$this->getSource()->value}", [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error("News API request exception for {$this->getSource()->value}", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if the service is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }

    /**
     * Create ArticleData object from array
     *
     * @param array $data
     * @return ArticleData
     */
    protected function createArticleData(array $data): ArticleData
    {
        return new ArticleData(
            title: $data['title'] ?? '',
            url: $data['url'] ?? '',
            source: $this->getSource()->value,
            externalId: $data['external_id'] ?? '',
            description: $data['description'] ?? null,
            content: $data['content'] ?? null,
            author: $data['author'] ?? null,
            category: $data['category'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            publishedAt: $data['published_at'] ?? now(),
        );
    }
}
