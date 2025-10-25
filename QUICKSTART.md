# Quick Start Guide - News Aggregator

## 🚀 First Time Setup (5 minutes)

### 1️⃣ Install Dependencies
```bash
composer install
```

### 2️⃣ Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3️⃣ Add Your API Keys
Edit `.env` file and add:
```env
NEWSAPI_KEY=your_key_here
GUARDIAN_API_KEY=your_key_here
NYTIMES_API_KEY=your_key_here
```

Get keys from:
- NewsAPI: https://newsapi.org/register
- Guardian: https://bonobo.capi.gutools.co.uk/register/developer
- NYTimes: https://developer.nytimes.com/get-started

### 4️⃣ Setup Database
```bash
php artisan migrate
php artisan db:seed
```

This creates:
- ✅ All database tables
- ✅ Test user: `test@example.com` / `password`

### 5️⃣ Generate API Documentation
```bash
php artisan l5-swagger:generate
```

This generates the Swagger UI for testing API endpoints.

### 6️⃣ Fetch Initial News (takes ~1 minute)
```bash
php artisan articles:fetch --limit=10
```

This fetches 10 articles from each configured news source.

> **⚠️ Important:** Don't skip this step! Without it, your database will be empty until the scheduler runs (which could take up to 1 hour).

### 7️⃣ Start Server
```bash
php artisan serve
```

✅ **Done!** 
- **Swagger UI:** http://localhost:8000/api/documentation
- **API:** http://localhost:8000/api/articles

> **⚠️ Important:** To keep news updated automatically, see "Keep News Updated Automatically" section below.

---

## 📊 Quick Setup Summary

Total time: **~5 minutes**

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Add API keys to .env (get free keys from providers)

# 4. Setup database
php artisan migrate
php artisan db:seed

# 5. Generate API documentation (Swagger UI)
php artisan l5-swagger:generate

# 6. Fetch initial news
php artisan articles:fetch --limit=10

# 7. Start server
php artisan serve

# 8. (OPTIONAL) Start scheduler for automatic updates
# Open a new terminal and run:
php artisan schedule:work
```

> **Note:** Without the scheduler, articles won't update automatically. You'll need to manually run `php artisan articles:fetch` to get new articles.

> **Test APIs:** Visit http://localhost:8000/api/documentation to use Swagger UI

---

## 🔄 Keep News Updated Automatically

**Without this step, articles will NOT update automatically!**

### Development (Recommended - Easy Way)
Open a **new terminal** (keep the server running) and run:
```bash
php artisan schedule:work
```

Leave this running in the background. It will:
- ✅ Fetch new articles every hour (20 articles per source)
- ✅ Clean up old articles daily

> **📌 Note:** The scheduler runs jobs at the **top of each hour** (e.g., 2:00, 3:00, 4:00). If you skip the initial fetch in step 6, you might wait up to 1 hour before seeing any articles!

### Production (Windows)
Double-click: `schedule-runner.bat` and set up Windows Task Scheduler (see README.md)

---

## 🧪 Test the API

### Use Swagger UI (Recommended)

Visit **http://localhost:8000/api/documentation** to test all endpoints interactively.

**Quick Test Steps:**
1. Open Swagger UI in your browser
2. Try `GET /api/articles` to see the fetched articles
3. Click "Authorize" and login with `test@example.com` / `password` to get a token
4. Test protected endpoints like preferences

### Manual Commands

```bash
# Fetch more articles anytime
php artisan articles:fetch

# Cleanup old articles (older than 30 days)
php artisan articles:cleanup
```

---

## 📚 Next Steps

1. **Test APIs with Swagger UI**: http://localhost:8000/api/documentation
2. **Read full docs**: [README.md](README.md)
3. **API Documentation**: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
4. **Run tests**: `php artisan test`

---

## 🆘 Common Issues

### "No articles found"
Run: `php artisan articles:fetch`

### "API key invalid"
Check your `.env` file has correct keys

### "Scheduler not working"
Run: `php artisan schedule:work` in a separate terminal

### "Tests failing"
Run: `php artisan migrate --env=testing`
