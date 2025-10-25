# 🚀 First Launch Guide for Developers

## Overview

This guide explains how to set up the News Aggregator application for the first time and how the automated job execution works.

---

## 📦 Prerequisites

Before you begin, ensure you have:

- **PHP 8.2+** installed
- **Composer** installed
- **MySQL/MariaDB** running (via XAMPP or similar)
- **Git** (for cloning the repository)

---

## 🛠️ Initial Setup (First Time Only)

### Step 1: Clone and Install Dependencies

```bash
# Clone the repository
git clone <repository-url>
cd news-aggregator

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 2: Configure Environment

Edit the `.env` file and configure:

#### **Database Configuration:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_aggregator
DB_USERNAME=root
DB_PASSWORD=
```

#### **News API Keys** (at least one required):
```env
# NewsAPI.org (https://newsapi.org/register)
NEWSAPI_KEY=your_newsapi_key_here

# The Guardian (https://open-platform.theguardian.com/access/)
GUARDIAN_API_KEY=your_guardian_key_here

# New York Times (https://developer.nytimes.com/get-started)
NYTIMES_API_KEY=your_nytimes_key_here
```

> **Note:** You need at least ONE API key configured. The application will fetch from all configured sources.

### Step 3: Create Database

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE news_aggregator;"
```

### Step 4: Run Automated Setup

**Option A: Complete Automated Setup (Recommended)**

```bash
php artisan app:setup
```

This will:
- ✅ Check your environment configuration
- ✅ Run database migrations
- ✅ Fetch 50 initial articles from all configured sources
- ✅ Display statistics
- ✅ Show next steps

**Option B: Manual Setup**

```bash
# 1. Run migrations and seed test user
php artisan migrate
php artisan db:seed

# 2. Fetch initial articles
php artisan articles:fetch --limit=50

# 3. Check status
php artisan articles:fetch --limit=0
```

> **Note:** Test user credentials: `test@example.com` / `password`

---

## 🔄 How the First Job Execution Works

### **Understanding the Job System**

The application uses Laravel's **Job Queue** system for fetching news articles. Here's how it works:

#### **1. The FetchNewsArticlesJob**

**Location:** `app/Jobs/FetchNewsArticlesJob.php`

**What it does:**
- Fetches articles from all configured news APIs
- Stores them in the database
- Logs success/failures
- Can run synchronously or in the queue

#### **2. First Execution Methods**

There are **4 ways** to trigger the first job execution:

##### **Method 1: Automated Setup Command** ⭐ **RECOMMENDED**

```bash
php artisan app:setup
```

- Runs the job **synchronously** (you see immediate results)
- Fetches 50 articles per source
- Shows statistics when complete
- Perfect for first-time setup

##### **Method 2: Direct Artisan Command**

```bash
# Synchronous (waits for completion)
php artisan articles:fetch --limit=50

# Or dispatch to queue (background)
php artisan articles:fetch --limit=50 --queue
```

##### **Method 3: Dispatch from Code**

```php
use App\Jobs\FetchNewsArticlesJob;

// Run immediately (synchronous)
FetchNewsArticlesJob::dispatchSync(50);

// Or queue for background processing
FetchNewsArticlesJob::dispatch(50);
```

##### **Method 4: Via Scheduler** (After setup)

The job is automatically scheduled to run **every hour**:

```php
// routes/console.php
Schedule::job(new FetchNewsArticlesJob(limit: 20))
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
```

---

## ⚙️ Setting Up Automated/Repeated Execution

### **Development Environment**

#### **Option A: Scheduler Worker (Easiest)**

```bash
# Run this in a terminal (keeps running)
php artisan schedule:work
```

Or on **Windows**, double-click: `run-scheduler.bat`

This will:
- Check for scheduled tasks every minute
- Run `FetchNewsArticlesJob` every hour
- Run cleanup daily at 2:00 AM

#### **Option B: Queue Worker**

If you want to process queued jobs:

```bash
# Run this in a separate terminal
php artisan queue:work
```

Then dispatch jobs to the queue:
```bash
php artisan articles:fetch --queue
```

### **Production Environment**

#### **Linux/Mac: Cron Job**

Add to your crontab (`crontab -e`):

```bash
* * * * * cd /path/to/news-aggregator && php artisan schedule:run >> /dev/null 2>&1
```

This runs **every minute** and checks if any scheduled tasks should execute.

#### **Windows: Task Scheduler**

1. Open **Task Scheduler**
2. Create new task:
   - **Program:** `C:\path\to\news-aggregator\schedule-runner.bat`
   - **Trigger:** Every 1 minute
   - **Duration:** Indefinitely

---

## 📊 Verification

### Check if Jobs are Running

```bash
# Check database for recent articles
php artisan tinker
>>> App\Models\Article::latest()->take(5)->get(['title', 'created_at']);

# Check logs
cat storage/logs/laravel.log | grep "News fetch job"
```

### View Scheduled Tasks

```bash
php artisan schedule:list
```

Output:
```
0 * * * *  App\Jobs\FetchNewsArticlesJob ..... Next Due: 23 minutes from now
0 2 * * *  articles:cleanup --days=30 ..... Next Due: 12 hours from now
```

---

## 🎯 Complete First Launch Workflow

Here's the **recommended workflow** for a developer running the app for the first time:

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
# Edit .env with database and API keys

# 3. Create database
mysql -u root -e "CREATE DATABASE news_aggregator;"

# 4. Run automated setup
php artisan app:setup

# 5. Start development server (Terminal 1)
php artisan serve

# 6. Start scheduler (Terminal 2)
php artisan schedule:work

# 7. (Optional) Start queue worker (Terminal 3)
php artisan queue:work
```

### Access the Application

- **API:** http://localhost:8000/api/articles
- **Documentation:** http://localhost:8000/api-docs
- **Register User:** POST http://localhost:8000/api/register
- **Login:** POST http://localhost:8000/api/login

---

## 🔍 Troubleshooting

### Job Not Running Automatically?

**Check scheduler is running:**
```bash
ps aux | grep "schedule:work"
```

**Manually trigger to test:**
```bash
php artisan schedule:run
```

### No Articles Being Fetched?

**Check API keys:**
```bash
php artisan app:setup --skip-migrations --skip-fetch
```

**Test API connection:**
```bash
php artisan articles:fetch --source=newsapi --limit=5
```

### Queue Jobs Not Processing?

**Ensure queue worker is running:**
```bash
php artisan queue:work --once
```

**Check failed jobs:**
```bash
php artisan queue:failed
```

---

## 📝 Summary

### **For First Launch:**
1. Run `php artisan app:setup` - **Fetches initial data**
2. Start `php artisan serve` - **Runs web server**
3. Start `php artisan schedule:work` - **Enables hourly auto-fetch**

### **What Happens Automatically:**
- **Every hour:** `FetchNewsArticlesJob` runs (fetches 20 articles/source)
- **Daily at 2 AM:** Old articles cleanup (30+ days old)

### **Job Execution Flow:**

```
Developer runs app for first time
         ↓
php artisan app:setup
         ↓
FetchNewsArticlesJob::dispatchSync(50)  ← Immediate execution
         ↓
Database populated with initial articles
         ↓
php artisan schedule:work starts
         ↓
Every hour at :00
         ↓
Schedule::job(new FetchNewsArticlesJob(20))
         ↓
Job executes (queued or sync)
         ↓
New articles added to database
         ↓
Repeat every hour ∞
```

---

## 🆘 Need Help?

- Check `README.md` for general documentation
- See `QUICKSTART.md` for a 5-minute setup
- Read `API_DOCUMENTATION.md` for API usage
- View `docs/openapi.yaml` for API specifications

---

**Happy Coding! 🎉**
