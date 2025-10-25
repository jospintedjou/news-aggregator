<?php

namespace App\Console\Commands;

use App\Repositories\ArticleRepository;
use Illuminate\Console\Command;

class CleanupOldArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:cleanup 
                            {--days=30 : Delete articles older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old articles from the database to free up storage';

    /**
     * Execute the console command.
     */
    public function handle(ArticleRepository $repository): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('Days must be greater than 0');
            return Command::FAILURE;
        }

        $this->info("Deleting articles older than {$days} days...");

        if (!$this->confirm('Are you sure you want to proceed?', true)) {
            $this->info('Cleanup cancelled.');
            return Command::SUCCESS;
        }

        try {
            $deleted = $repository->deleteOlderThan($days);

            if ($deleted > 0) {
                $this->info("✓ Successfully deleted {$deleted} article(s)");
            } else {
                $this->info('No articles to delete.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

