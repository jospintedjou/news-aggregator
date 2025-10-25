<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class ArticleData
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $source,
        public readonly string $externalId,
        public readonly ?string $description = null,
        public readonly ?string $content = null,
        public readonly ?string $author = null,
        public readonly ?string $category = null,
        public readonly ?string $imageUrl = null,
        public readonly ?Carbon $publishedAt = null,
    ) {}

    /**
     * Convert to array format for database insertion
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'author' => $this->author,
            'source' => $this->source,
            'category' => $this->category,
            'url' => $this->url,
            'image_url' => $this->imageUrl,
            'external_id' => $this->externalId,
            'published_at' => $this->publishedAt,
        ];
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            url: $data['url'] ?? '',
            source: $data['source'] ?? '',
            externalId: $data['external_id'] ?? '',
            description: $data['description'] ?? null,
            content: $data['content'] ?? null,
            author: $data['author'] ?? null,
            category: $data['category'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            publishedAt: isset($data['published_at']) && $data['published_at'] instanceof Carbon 
                ? $data['published_at'] 
                : (isset($data['published_at']) ? Carbon::parse($data['published_at']) : null),
        );
    }
}
