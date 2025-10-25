<?php

namespace App\Console\Commands;

use App\Jobs\FetchNewsArticlesJob;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup 
                            {--skip-migrations : Skip running migrations}
                            {--skip-fetch : Skip fetching initial articles}
                            {--limit=20 : Number of articles to fetch per source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'First-time setup for the news aggregator application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting News Aggregator Setup...');
        $this->newLine();

        // Step 1: Check environment
        if (!$this->checkEnvironment()) {
            return Command::FAILURE;
        }

        // Step 2: Run migrations
        if (!$this->option('skip-migrations')) {
            $this->runMigrations();
        }

        // Step 3: Fetch initial articles
        if (!$this->option('skip-fetch')) {
            $this->fetchInitialArticles();
        }

        // Step 4: Show statistics
        $this->showStatistics();

        // Step 5: Show next steps
        $this->showNextSteps();

        $this->newLine();
        $this->info('✅ Setup completed successfully!');
        
        return Command::SUCCESS;
    }

    /**
     * Check if environment is properly configured
     */
    protected function checkEnvironment(): bool
    {
        $this->info('📋 Step 1: Checking environment configuration...');

        $required = [
            'NEWSAPI_KEY' => 'NewsAPI',
            'GUARDIAN_API_KEY' => 'The Guardian',
            'NYTIMES_API_KEY' => 'New York Times',
        ];

        $missing = [];
        $configured = [];

        foreach ($required as $key => $name) {
            if (!env($key)) {
                $missing[] = "$name ($key)";
            } else {
                $configured[] = $name;
            }
        }

        if (!empty($configured)) {
            $this->line('  ✓ Configured: ' . implode(', ', $configured));
        }

        if (!empty($missing)) {
            $this->warn('  ⚠ Missing API keys: ' . implode(', ', $missing));
            $this->newLine();
            $this->line('  Add these to your .env file to enable all sources.');
        }

        $this->newLine();
        return true;
    }

    /**
     * Run database migrations
     */
    protected function runMigrations(): void
    {
        $this->info('📦 Step 2: Running database migrations...');

        try {
            Artisan::call('migrate', ['--force' => true], $this->getOutput());
            $this->line('  ✓ Migrations completed');
        } catch (\Exception $e) {
            $this->error('  ✗ Migration failed: ' . $e->getMessage());
        }

        // Seed database with test user
        try {
            $this->line('  Seeding test user...');
            Artisan::call('db:seed', ['--force' => true], $this->getOutput());
        } catch (\Exception $e) {
            // Seeding error is not critical, continue
            $this->line('  Note: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Fetch initial articles
     */
    protected function fetchInitialArticles(): void
    {
        $limit = (int) $this->option('limit');
        
        $this->info("📰 Step 3: Fetching initial articles ($limit per source)...");

        try {
            $progressBar = $this->output->createProgressBar(3);
            $progressBar->setFormat(' %current%/%max% [%bar%] %message%');
            
            // Start progress
            $progressBar->setMessage('Fetching from NewsAPI...');
            $progressBar->start();
            
            // Run the fetch job synchronously for first setup
            FetchNewsArticlesJob::dispatchSync($limit);
            
            $progressBar->setMessage('Complete!');
            $progressBar->finish();
            $this->newLine(2);
            
            $this->line('  ✓ Articles fetched successfully');
        } catch (\Exception $e) {
            $this->error('  ✗ Fetch failed: ' . $e->getMessage());
            $this->line('  You can retry with: php artisan articles:fetch --limit=' . $limit);
        }

        $this->newLine();
    }

    /**
     * Show database statistics
     */
    protected function showStatistics(): void
    {
        $this->info('📊 Step 4: Database Statistics');

        try {
            $totalArticles = Article::count();
            $sourceStats = Article::select('source', DB::raw('count(*) as count'))
                ->groupBy('source')
                ->get();

            $this->table(
                ['Source', 'Articles'],
                $sourceStats->map(fn($stat) => [
                    $stat->source->label(),
                    $stat->count
                ])->toArray()
            );

            $this->line("  Total Articles: $totalArticles");
        } catch (\Exception $e) {
            $this->warn('  Could not retrieve statistics: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Show next steps for the developer
     */
    protected function showNextSteps(): void
    {
        $this->info('📚 Next Steps:');
        $this->newLine();

        $this->line('  1️⃣  Start the development server:');
        $this->line('     php artisan serve');
        $this->newLine();

        $this->line('  2️⃣  Start the scheduler to auto-fetch news every hour:');
        $this->line('     php artisan schedule:work');
        $this->line('     (Or on Windows: double-click run-scheduler.bat)');
        $this->newLine();

        $this->line('  3️⃣  Access the API:');
        $this->line('     http://localhost:8000/api/articles');
        $this->newLine();

        $this->line('  4️⃣  View API documentation:');
        $this->line('     http://localhost:8000/api-docs');
        $this->newLine();

        $this->line('  📖 For more details, see:');
        $this->line('     - README.md');
        $this->line('     - QUICKSTART.md');
        $this->line('     - API_DOCUMENTATION.md');
    }
}
