@echo off
echo Starting Laravel Scheduler...
echo This will run continuously and execute scheduled tasks
echo Press Ctrl+C to stop
echo.
cd /d C:\xampp\htdocs\news-aggregator
php artisan schedule:work
