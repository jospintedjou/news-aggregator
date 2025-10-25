<?php

use App\Jobs\FetchNewsArticlesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the article fetching job to run every hour
Schedule::job(new FetchNewsArticlesJob(limit: 20))
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

// Alternative: Run command directly (synchronous)
// Schedule::command('articles:fetch --limit=20')->hourly();

// Schedule cleanup of old articles (keep last 30 days)
Schedule::command('articles:cleanup --days=30')
    ->dailyAt('02:00')
    ->name('cleanup-old-articles')
    ->onOneServer();

