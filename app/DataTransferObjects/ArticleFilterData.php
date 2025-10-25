<?php

namespace App\DataTransferObjects;

use App\Enums\NewsSource;

readonly class ArticleFilterData
{
    public function __construct(
        public ?string $keyword = null,
        public ?NewsSource $source = null,
        public ?string $category = null,
        public ?string $author = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?bool $ignorePreferences = null,
        public int $perPage = 15,
    ) {}

    /**
     * Create from request data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            keyword: $data['q'] ?? null,
            source: isset($data['source']) ? NewsSource::from($data['source']) : null,
            category: $data['category'] ?? null,
            author: $data['author'] ?? null,
            dateFrom: $data['from'] ?? null,
            dateTo: $data['to'] ?? null,
            ignorePreferences: $data['ignore_preferences'] ?? null,
            perPage: (int) ($data['per_page'] ?? 15),
        );
    }

    /**
     * Convert to array for repository
     */
    public function toArray(): array
    {
        return [
            'q' => $this->keyword,
            'source' => $this->source?->value,
            'category' => $this->category,
            'author' => $this->author,
            'from' => $this->dateFrom,
            'to' => $this->dateTo,
            'ignore_preferences' => $this->ignorePreferences,
        ];
    }
}
