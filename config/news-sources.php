<?php

return [
    /*
    |--------------------------------------------------------------------------
    | News API Sources Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the news API sources that will be used to fetch articles.
    | Add your API keys to the .env file for security.
    |
    */

    'newsapi' => [
        'name' => 'NewsAPI',
        'base_url' => 'https://newsapi.org/v2',
        'api_key' => env('NEWSAPI_KEY'),
        'enabled' => env('NEWSAPI_ENABLED', true),
        'endpoints' => [
            'top_headlines' => '/top-headlines',
            'everything' => '/everything',
        ],
        'rate_limit' => [
            'requests_per_day' => 100,
            'requests_per_hour' => 100,
        ],
    ],

    'guardian' => [
        'name' => 'The Guardian',
        'base_url' => 'https://content.guardianapis.com',
        'api_key' => env('GUARDIAN_API_KEY'),
        'enabled' => env('GUARDIAN_ENABLED', true),
        'endpoints' => [
            'search' => '/search',
        ],
        'rate_limit' => [
            'requests_per_day' => 5000,
            'requests_per_second' => 12,
        ],
    ],

    'nytimes' => [
        'name' => 'New York Times',
        'base_url' => 'https://api.nytimes.com/svc',
        'api_key' => env('NYTIMES_API_KEY'),
        'enabled' => env('NYTIMES_ENABLED', true),
        'endpoints' => [
            'top_stories' => '/topstories/v2',
            'article_search' => '/search/v2/articlesearch.json',
        ],
        'rate_limit' => [
            'requests_per_day' => 500,
            'requests_per_minute' => 10,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    */

    'fetch_interval' => env('NEWS_FETCH_INTERVAL', 3600), // seconds (1 hour default)
    'articles_per_source' => env('ARTICLES_PER_SOURCE', 100),
    'timeout' => 30,
];
