<?php

namespace App\Services\NewsAggregator;

use App\Enums\NewsSource;
use App\Services\NewsAggregator\Contracts\NewsServiceInterface;
use Illuminate\Support\Collection;

class NewsAggregatorService
{
    /**
     * Get service instance for a specific news source
     *
     * @param NewsSource $source
     * @return NewsServiceInterface|null
     */
    public function getService(NewsSource $source): ?NewsServiceInterface
    {
        return match($source) {
            NewsSource::NEWSAPI => app(NewsAPIService::class),
            NewsSource::GUARDIAN => app(GuardianService::class),
            NewsSource::NYTIMES => app(NYTimesService::class),
        };
    }

    /**
     * Get all enabled news services
     *
     * @return Collection<NewsServiceInterface>
     */
    public function getEnabledServices(): Collection
    {
        return collect(NewsSource::enabled())
            ->map(fn($source) => $this->getService($source))
            ->filter(fn($service) => $service && $service->isConfigured());
    }

    /**
     * Fetch articles from all enabled sources
     *
     * @param array $params
     * @return array
     */
    public function fetchFromAllSources(array $params = []): array
    {
        $articles = [];

        foreach ($this->getEnabledServices() as $service) {
            $fetchedArticles = $service->fetchArticles($params);
            $articles = array_merge($articles, $fetchedArticles);
        }

        return $articles;
    }

    /**
     * Fetch articles from specific source
     *
     * @param NewsSource $source
     * @param array $params
     * @return array
     */
    public function fetchFromSource(NewsSource $source, array $params = []): array
    {
        $service = $this->getService($source);

        if (!$service || !$service->isConfigured()) {
            return [];
        }

        return $service->fetchArticles($params);
    }
}
