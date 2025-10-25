<?php

namespace App\Enums;

enum NewsSource: string
{
    case NEWSAPI = 'newsapi';
    case GUARDIAN = 'guardian';
    case NYTIMES = 'nytimes';

    /**
     * Get the display name of the news source
     */
    public function label(): string
    {
        return match($this) {
            self::NEWSAPI => 'NewsAPI',
            self::GUARDIAN => 'The Guardian',
            self::NYTIMES => 'New York Times',
        };
    }

    /**
     * Get the configuration for this news source
     */
    public function config(): array
    {
        return config('news-sources.' . $this->value, []);
    }

    /**
     * Check if this source is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config()['enabled'] ?? false;
    }

    /**
     * Get all enabled sources
     */
    public static function enabled(): array
    {
        return array_filter(self::cases(), fn($source) => $source->isEnabled());
    }

    /**
     * Get all source values as array
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get all source labels
     */
    public static function labels(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
    }
}
