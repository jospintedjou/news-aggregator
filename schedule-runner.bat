@echo off
REM Laravel Scheduler Runner for Windows
REM This script should run every minute via Windows Task Scheduler

cd /d C:\xampp\htdocs\news-aggregator
php artisan schedule:run >> storage/logs/scheduler.log 2>&1
