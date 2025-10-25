<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsArticlesJob;
use App\Repositories\ArticleRepository;
use App\Services\NewsAggregator\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:fetch 
                            {--source= : Specific source to fetch from (newsapi, guardian, nytimes)}
                            {--limit=100 : Number of articles to fetch per source}
                            {--queue : Dispatch the job to the queue instead of running synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from news APIs and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $aggregator, ArticleRepository $repository): int
    {
        $limit = (int) $this->option('limit');
        $useQueue = $this->option('queue');

        // If queue option is used, dispatch job
        if ($useQueue) {
            FetchNewsArticlesJob::dispatch($limit);
            $this->info('✓ News fetch job dispatched to queue');
            return Command::SUCCESS;
        }

        // Otherwise run synchronously
        $this->info('Starting article fetch...');

        $source = $this->option('source');

        try {
            if ($source) {
                // Fetch from specific source
                $this->fetchFromSpecificSource($aggregator, $repository, $source, $limit);
            } else {
                // Fetch from all enabled sources
                $this->fetchFromAllSources($aggregator, $repository, $limit);
            }

            $this->newLine();
            $this->info('✓ Article fetch completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Fetch articles from all enabled sources
     */
    protected function fetchFromAllSources(
        NewsAggregatorService $aggregator,
        ArticleRepository $repository,
        int $limit
    ): void {
        $services = $aggregator->getEnabledServices();

        if ($services->isEmpty()) {
            $this->warn('No enabled news sources found. Please configure API keys in .env');
            return;
        }

        $this->info("Fetching from {$services->count()} source(s)...");
        $this->newLine();

        $totalInserted = 0;

        foreach ($services as $service) {
            $sourceName = $service->getSource()->label();
            
            $this->line("Fetching from {$sourceName}...");

            $articles = $service->fetchArticles(['pageSize' => $limit]);
            
            if (empty($articles)) {
                $this->warn("  ⚠ No articles fetched from {$sourceName}");
                continue;
            }

            $inserted = $repository->bulkUpsert($articles);
            $totalInserted += $inserted;

            $this->info("  ✓ Fetched " . count($articles) . " articles, inserted/updated {$inserted}");
        }

        $this->newLine();
        $this->info("Total articles inserted/updated: {$totalInserted}");
    }

    /**
     * Fetch articles from a specific source
     */
    protected function fetchFromSpecificSource(
        NewsAggregatorService $aggregator,
        ArticleRepository $repository,
        string $sourceName,
        int $limit
    ): void {
        // Convert string to NewsSource enum
        $sourceEnum = match(strtolower($sourceName)) {
            'newsapi' => \App\Enums\NewsSource::NEWSAPI,
            'guardian' => \App\Enums\NewsSource::GUARDIAN,
            'nytimes' => \App\Enums\NewsSource::NYTIMES,
            default => null,
        };

        if (!$sourceEnum) {
            $this->error("Invalid source: {$sourceName}. Valid sources: newsapi, guardian, nytimes");
            return;
        }

        $this->info("Fetching from {$sourceEnum->label()}...");

        $articles = $aggregator->fetchFromSource($sourceEnum, ['pageSize' => $limit]);

        if (empty($articles)) {
            $this->warn('No articles fetched. Check API configuration.');
            return;
        }

        $inserted = $repository->bulkUpsert($articles);

        $this->info("✓ Fetched " . count($articles) . " articles, inserted/updated {$inserted}");
    }
}

