# News Aggregator Backend

A Laravel-based news aggregator backend that fetches articles from multiple news APIs (NewsAPI, The Guardian, New York Times) and serves them through a RESTful API with user personalization features.

## Features

✅ **Multi-Source Aggregation** - Fetches from 3 major news APIs  
✅ **User Preferences** - Personalized feeds based on sources, categories, authors, and keywords  
✅ **Advanced Filtering** - Search, date range, source, category, and author filters  
✅ **Automated Fetching** - Scheduled hourly article updates  
✅ **Repository Pattern** - Clean, maintainable architecture  
✅ **SOLID Principles** - Service contracts and dependency injection  
✅ **DTO Pattern** - Type-safe data transfer objects  
✅ **RESTful API** - Well-documented endpoints with proper resources  

---

## Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **APIs**: NewsAPI, The Guardian, New York Times
- **Patterns**: Repository, DTO, Service Layer, SOLID

---

## Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd news-aggregator
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure API Keys
Edit `.env` and add your API keys:
```env
# News API (https://newsapi.org/)
NEWSAPI_KEY=your_newsapi_key_here
NEWSAPI_ENABLED=true

# The Guardian (https://open-platform.theguardian.com/)
GUARDIAN_API_KEY=your_guardian_key_here
GUARDIAN_ENABLED=true

# New York Times (https://developer.nytimes.com/)
NYTIMES_API_KEY=your_nytimes_key_here
NYTIMES_ENABLED=true
```

**Get Your API Keys:**
- NewsAPI: https://newsapi.org/register
- The Guardian: https://bonobo.capi.gutools.co.uk/register/developer
- NY Times: https://developer.nytimes.com/get-started

### 5. Run Migrations & Seed Database
```bash
php artisan migrate
php artisan db:seed
```

This creates the database tables and seeds a test user:
- **Email:** test@example.com
- **Password:** password

### 6. Generate API Documentation
```bash
php artisan l5-swagger:generate
```

This generates the Swagger/OpenAPI documentation for interactive API testing.

### 7. Fetch Initial Articles
```bash
php artisan articles:fetch --limit=10
```

This fetches 10 articles from each configured news source (takes ~1 minute).

**For more articles:**
```bash
php artisan articles:fetch --limit=50
```

**Automated setup (optional):**
```bash
php artisan app:setup
```

### 8. Start Development Server
```bash
php artisan serve
```

The API will be available at:
- **Swagger UI:** `http://localhost:8000/api/documentation`
- **API Endpoints:** `http://localhost:8000/api`

---

## 🔄 Automated News Updates

To keep your news database updated automatically, you need to set up the Laravel scheduler:

### **Option 1: Development (Recommended for Local)**

Run the scheduler in the foreground (stays running):
```bash
php artisan schedule:work
```

Or double-click the provided batch file:
```
run-scheduler.bat  (Windows)
```

This will check for scheduled tasks every minute and run them automatically.

### **Option 2: Production (Windows with XAMPP)**

Set up **Windows Task Scheduler** to run every minute:

1. Open **Task Scheduler** (Win + R → `taskschd.msc`)
2. Click "Create Basic Task"
3. Name: "Laravel News Scheduler"
4. Trigger: Daily
5. Action: Start a program
   - Program: `C:\xampp\htdocs\news-aggregator\schedule-runner.bat`
6. Advanced settings:
   - ✅ Repeat task every: **1 minute**
   - ✅ For a duration of: Indefinitely
   - ✅ Run whether user is logged on or not

### **Option 3: Production (Linux/Mac)**

Add to crontab:
```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/news-aggregator && php artisan schedule:run >> /dev/null 2>&1
```

### **Scheduled Tasks Summary**

Once the scheduler is running, these tasks execute automatically:

| Task | Frequency | Command |
|------|-----------|---------|
| Fetch new articles | Every hour | `articles:fetch` |
| Cleanup old articles | Daily at 2:00 AM | `articles:cleanup --days=30` |

### **Manual Execution**

You can also run tasks manually anytime:
```bash
# Fetch articles now
php artisan articles:fetch

# Cleanup old articles now
php artisan articles:cleanup

# Test the scheduler
php artisan schedule:list
```

---

## API Endpoints

### Public Endpoints
```
GET    /api/articles          - List articles (with filters)
GET    /api/articles/{id}     - Get single article
GET    /api/sources           - Get available sources
GET    /api/categories        - Get available categories
GET    /api/authors           - Get available authors
```

### Protected Endpoints (require authentication)
```
GET    /api/preferences       - Get user preferences
POST   /api/preferences       - Save user preferences
DELETE /api/preferences       - Delete user preferences
```

📖 **Full API Documentation**: See [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

---

## 🧪 Testing the API

### Interactive Testing with Swagger UI

Visit **http://localhost:8000/api/documentation** to test all endpoints interactively using Swagger UI.

The Swagger UI provides:
- ✅ Full API documentation
- ✅ Interactive endpoint testing
- ✅ Request/response examples
- ✅ Authentication support
- ✅ No need for curl commands

**Quick Test:**
1. Open http://localhost:8000/api/documentation
2. Try the `GET /api/articles` endpoint
3. For protected endpoints, use the "Authorize" button with your token

---

## Artisan Commands

### Fetch Articles
```bash
# Fetch from all enabled sources
php artisan articles:fetch

# Fetch from specific source
php artisan articles:fetch --source=newsapi
php artisan articles:fetch --source=guardian --limit=50
```

### Cleanup Old Articles
```bash
# Delete articles older than 30 days (default)
php artisan articles:cleanup

# Custom retention period
php artisan articles:cleanup --days=90
```

### Application Setup
```bash
# Run initial setup (first launch)
php artisan app:setup

# Force re-setup even if articles exist
php artisan app:setup --force
```

---

## Project Structure

```
app/
├── Console/Commands/
│   ├── FetchArticlesCommand.php
│   └── CleanupOldArticlesCommand.php
├── DataTransferObjects/
│   └── ArticleData.php
├── Enums/
│   └── NewsSource.php
├── Http/
│   ├── Controllers/
│   │   ├── ArticleController.php
│   │   └── PreferenceController.php
│   ├── Requests/
│   │   └── StorePreferenceRequest.php
│   └── Resources/
│       ├── ArticleResource.php
│       └── ArticleCollection.php
├── Models/
│   ├── Article.php
│   ├── User.php
│   └── UserPreference.php
├── Repositories/
│   └── ArticleRepository.php
└── Services/
    └── NewsAggregator/
        ├── Contracts/
        │   └── NewsServiceInterface.php
        ├── BaseNewsService.php
        ├── GuardianService.php
        ├── NewsAPIService.php
        ├── NYTimesService.php
        └── NewsAggregatorService.php

config/
└── news-sources.php

database/
├── migrations/
│   ├── 2025_10_24_102900_create_articles_table.php
│   └── 2025_10_24_115437_create_user_preferences_table.php

routes/
├── api.php
└── console.php
```

---

## Architecture & Design Patterns

### Repository Pattern
- `ArticleRepository` - Abstracts database queries and business logic
- Provides filtering, searching, and preference application

### Service Layer
- `NewsServiceInterface` - Contract for news services
- `BaseNewsService` - Shared HTTP and transformation logic
- Concrete services for each API (NewsAPI, Guardian, NYTimes)

### DTO Pattern
- `ArticleData` - Type-safe data transfer object
- Immutable readonly properties
- Easy conversion to/from arrays

### SOLID Principles
- **Single Responsibility**: Each class has one purpose
- **Open/Closed**: Extensible without modification
- **Liskov Substitution**: All services implement same interface
- **Interface Segregation**: Clean, focused interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ArticleTest
```

---

## Configuration

### News Sources
Edit `config/news-sources.php` to configure:
- API endpoints
- Rate limits
- Default fetch settings

### Retention Policy
Edit `.env`:
```env
NEWS_FETCH_INTERVAL=3600    # Seconds between fetches
ARTICLES_PER_SOURCE=100      # Articles per source per fetch
```

---

## Troubleshooting

### No articles fetched
1. Check API keys in `.env`
2. Verify API key validity
3. Check rate limits
4. Review logs: `storage/logs/laravel.log`

### Scheduling not working
- Ensure cron is set up correctly
- Check `php artisan schedule:list`
- Test manually: `php artisan schedule:run`

---

## License

This project is open-sourced software licensed under the MIT license.

---

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

---

## Credits

Built with ❤️ using Laravel and following best practices for clean, maintainable code.

**News APIs:**
- [NewsAPI](https://newsapi.org/)
- [The Guardian Open Platform](https://open-platform.theguardian.com/)
- [New York Times Developer](https://developer.nytimes.com/)


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
