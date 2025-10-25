# News Aggregator API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
Some endpoints require authentication using Laravel Sanctum. Include the token in the Authorization header:
```
Authorization: Bearer {your-token}
```

---

## Public Endpoints

### 1. Get Articles
Retrieve a paginated list of articles with optional filters.

**Endpoint:** `GET /api/articles`

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| q | string | Search in title, description, content | `?q=technology` |
| source | string | Filter by source (comma-separated) | `?source=newsapi,guardian` |
| category | string | Filter by category (comma-separated) | `?category=business,tech` |
| author | string | Filter by author (comma-separated) | `?author=John Doe` |
| from | date | Filter from date (Y-m-d) | `?from=2025-10-01` |
| to | date | Filter to date (Y-m-d) | `?to=2025-10-24` |
| per_page | int | Results per page (default: 15) | `?per_page=20` |
| page | int | Page number | `?page=2` |
| ignore_preferences | bool | Ignore user preferences (authenticated only) | `?ignore_preferences=1` |

**Example Request:**
```bash
GET /api/articles?q=bitcoin&source=newsapi&per_page=10
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Bitcoin reaches new high",
      "description": "Cryptocurrency hits record price...",
      "content": "Full article content...",
      "author": "Jane Doe",
      "source": {
        "id": "newsapi",
        "name": "NewsAPI"
      },
      "category": "business",
      "url": "https://example.com/article",
      "image_url": "https://example.com/image.jpg",
      "published_at": "2025-10-24T10:00:00Z",
      "created_at": "2025-10-24T11:00:00Z"
    }
  ],
  "meta": {
    "total": 150,
    "per_page": 10,
    "current_page": 1,
    "last_page": 15
  }
}
```

---

### 2. Get Single Article
Retrieve a specific article by ID.

**Endpoint:** `GET /api/articles/{id}`

**Example Request:**
```bash
GET /api/articles/123
```

**Response:**
```json
{
  "data": {
    "id": 123,
    "title": "Article Title",
    "description": "Article description...",
    "content": "Full content...",
    "author": "John Smith",
    "source": {
      "id": "guardian",
      "name": "The Guardian"
    },
    "category": "technology",
    "url": "https://example.com/article",
    "image_url": "https://example.com/image.jpg",
    "published_at": "2025-10-24T10:00:00Z",
    "created_at": "2025-10-24T11:00:00Z"
  }
}
```

---

### 3. Get Available Sources
Get list of all news sources.

**Endpoint:** `GET /api/sources`

**Example Request:**
```bash
GET /api/sources
```

**Response:**
```json
{
  "data": [
    {
      "id": "newsapi",
      "name": "NewsAPI",
      "enabled": true
    },
    {
      "id": "guardian",
      "name": "The Guardian",
      "enabled": true
    },
    {
      "id": "nytimes",
      "name": "New York Times",
      "enabled": false
    }
  ]
}
```

---

### 4. Get Available Categories
Get list of all article categories.

**Endpoint:** `GET /api/categories`

**Example Request:**
```bash
GET /api/categories
```

**Response:**
```json
{
  "data": [
    "business",
    "technology",
    "sports",
    "entertainment",
    "science"
  ]
}
```

---

### 5. Get Available Authors
Get list of all article authors.

**Endpoint:** `GET /api/authors`

**Example Request:**
```bash
GET /api/authors
```

**Response:**
```json
{
  "data": [
    "John Doe",
    "Jane Smith",
    "Bob Johnson"
  ]
}
```

---

## Protected Endpoints (Authentication Required)

### 6. Get User Preferences
Get current user's article preferences.

**Endpoint:** `GET /api/preferences`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "preferred_sources": ["newsapi", "guardian"],
    "preferred_categories": ["technology", "business"],
    "preferred_authors": ["John Doe"],
    "keywords": ["AI", "crypto", "startup"]
  }
}
```

---

### 7. Save User Preferences
Create or update user preferences.

**Endpoint:** `POST /api/preferences`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "preferred_sources": ["newsapi", "guardian"],
  "preferred_categories": ["technology", "business", "science"],
  "preferred_authors": ["John Doe", "Jane Smith"],
  "keywords": ["AI", "machine learning", "blockchain"]
}
```

**Validation Rules:**
- `preferred_sources.*` - must be one of: newsapi, guardian, nytimes
- `preferred_categories.*` - string, max 255 characters
- `preferred_authors.*` - string, max 255 characters
- `keywords.*` - string, max 255 characters

**Response:**
```json
{
  "message": "Preferences saved successfully",
  "data": {
    "preferred_sources": ["newsapi", "guardian"],
    "preferred_categories": ["technology", "business", "science"],
    "preferred_authors": ["John Doe", "Jane Smith"],
    "keywords": ["AI", "machine learning", "blockchain"]
  }
}
```

---

### 8. Delete User Preferences
Delete all user preferences.

**Endpoint:** `DELETE /api/preferences`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Preferences deleted successfully"
}
```

---

## Preference Behavior

### Authenticated Users
When a user is authenticated and has saved preferences:

1. **Default behavior**: Articles are filtered by user preferences automatically
2. **Explicit filters**: Query parameters override preferences
3. **Ignore preferences**: Use `?ignore_preferences=1` to see global feed

### Guest Users
Unauthenticated users see the global feed with any applied query filters.

---

## Error Responses

### 404 Not Found
```json
{
  "message": "Article not found"
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "preferred_sources.0": [
      "Each source must be one of: newsapi, guardian, nytimes"
    ]
  }
}
```

---

## Artisan Commands

### Fetch Articles
```bash
# Fetch from all enabled sources
php artisan articles:fetch

# Fetch from specific source
php artisan articles:fetch --source=newsapi
php artisan articles:fetch --source=guardian
php artisan articles:fetch --source=nytimes

# Custom limit
php artisan articles:fetch --limit=200
```

### Cleanup Old Articles
```bash
# Delete articles older than 30 days (default)
php artisan articles:cleanup

# Custom retention period
php artisan articles:cleanup --days=90
```

---

## Scheduled Tasks

The following tasks run automatically:

- **Hourly**: Fetch new articles from all sources
- **Daily at 2 AM**: Cleanup articles older than 30 days

To enable scheduling, add to cron:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

For development:
```bash
php artisan schedule:work
```
