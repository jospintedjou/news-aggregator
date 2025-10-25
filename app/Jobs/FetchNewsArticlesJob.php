<?php

namespace App\Jobs;

use App\Repositories\ArticleRepository;
use App\Services\NewsAggregator\NewsAggregatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchNewsArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of articles to fetch per source
     */
    protected int $limit;

    /**
     * Create a new job instance.
     */
    public function __construct(int $limit = 20)
    {
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     */
    public function handle(
        NewsAggregatorService $aggregator,
        ArticleRepository $repository
    ): void {
        try {
            Log::info('Starting news fetch job', ['limit' => $this->limit]);

            // Fetch articles from all sources
            $articles = $aggregator->fetchFromAllSources(['limit' => $this->limit]);

            Log::info('Fetched articles from sources', [
                'total_fetched' => count($articles),
            ]);

            // Save articles to database
            if (!empty($articles)) {
                $saved = $repository->bulkUpsert($articles);
                
                Log::info('News fetch job completed', [
                    'fetched' => count($articles),
                    'saved' => $saved,
                ]);
            } else {
                Log::warning('No articles fetched from any source');
            }
        } catch (\Exception $e) {
            Log::error('News fetch job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('News fetch job permanently failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
