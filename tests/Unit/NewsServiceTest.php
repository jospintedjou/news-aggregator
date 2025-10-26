<?php

namespace Tests\Unit;

use App\Services\NewsAggregator\NewsAPIService;
use App\Services\NewsAggregator\GuardianService;
use App\Services\NewsAggregator\NYTimesService;
use App\DataTransferObjects\ArticleData;
use App\Enums\NewsSource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsServiceTest extends TestCase
{
    public function test_newsapi_transforms_article_correctly(): void
    {
        $service = new NewsAPIService();

        $apiArticle = [
            'title' => 'Test Article Title',
            'description' => 'Test Description',
            'content' => 'Test Content',
            'author' => 'John Doe',
            'source' => ['name' => 'BBC News'],
            'url' => 'https://example.com/article',
            'urlToImage' => 'https://example.com/image.jpg',
            'publishedAt' => '2024-01-15T10:30:00Z',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertEquals('Test Article Title', $result->title);
        $this->assertEquals('Test Description', $result->description);
        $this->assertEquals('Test Content', $result->content);
        $this->assertEquals('John Doe', $result->author);
        $this->assertEquals('newsapi', $result->source);
        $this->assertEquals('https://example.com/article', $result->url);
        $this->assertEquals('https://example.com/image.jpg', $result->imageUrl);
        $this->assertNotNull($result->publishedAt);
    }

    public function test_newsapi_handles_missing_optional_fields(): void
    {
        $service = new NewsAPIService();

        $apiArticle = [
            'title' => 'Test Article',
            'description' => null,
            'content' => null,
            'author' => null,
            'source' => ['name' => 'Unknown'],
            'url' => 'https://example.com/article',
            'urlToImage' => null,
            'publishedAt' => '2024-01-15T10:30:00Z',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertEquals('Test Article', $result->title);
        $this->assertNull($result->description);
        $this->assertNull($result->content);
        $this->assertNull($result->author);
    }

    public function test_guardian_transforms_article_correctly(): void
    {
        $service = new GuardianService();

        $apiArticle = [
            'webTitle' => 'Guardian Test Article',
            'fields' => [
                'trailText' => 'Test trail text',
                'body' => 'Full article body content',
                'byline' => 'Jane Smith',
                'thumbnail' => 'https://guardian.com/image.jpg',
            ],
            'webUrl' => 'https://guardian.com/article',
            'webPublicationDate' => '2024-01-15T10:30:00Z',
            'sectionName' => 'Technology',
            'id' => 'guardian-123',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertEquals('Guardian Test Article', $result->title);
        $this->assertEquals('Test trail text', $result->description);
        $this->assertEquals('Full article body content', $result->content);
        $this->assertEquals('Jane Smith', $result->author);
        $this->assertEquals('guardian', $result->source);
        $this->assertEquals('Technology', $result->category); // Guardian returns capitalized categories
        $this->assertEquals('https://guardian.com/article', $result->url);
        $this->assertEquals('https://guardian.com/image.jpg', $result->imageUrl);
        $this->assertEquals('guardian-123', $result->externalId);
    }

    public function test_guardian_handles_missing_fields(): void
    {
        $service = new GuardianService();

        $apiArticle = [
            'webTitle' => 'Guardian Article',
            'fields' => [],
            'webUrl' => 'https://guardian.com/article',
            'webPublicationDate' => '2024-01-15T10:30:00Z',
            'sectionName' => 'News',
            'id' => 'guardian-456',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertNull($result->description);
        $this->assertNull($result->content);
        $this->assertNull($result->author);
        $this->assertNull($result->imageUrl);
    }

    public function test_nytimes_transforms_article_correctly(): void
    {
        $service = new NYTimesService();

        $apiArticle = [
            'headline' => [
                'main' => 'NYTimes Test Article',
            ],
            'abstract' => 'Article abstract',
            'lead_paragraph' => 'Article lead paragraph content',
            'byline' => [
                'original' => 'By Bob Wilson',
            ],
            'web_url' => 'https://nytimes.com/article',
            'multimedia' => [
                ['url' => 'images/image.jpg'],
            ],
            'pub_date' => '2024-01-15T10:30:00Z',
            'section_name' => 'Business',
            '_id' => 'nyt-789',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertEquals('NYTimes Test Article', $result->title);
        $this->assertEquals('Article abstract', $result->description);
        $this->assertEquals('Article lead paragraph content', $result->content);
        $this->assertEquals('By Bob Wilson', $result->author);
        $this->assertEquals('nytimes', $result->source);
        $this->assertEquals('Business', $result->category); // NYTimes returns capitalized categories
        $this->assertEquals('https://nytimes.com/article', $result->url);
        $this->assertStringContainsString('nytimes.com', $result->imageUrl);
        $this->assertEquals('nyt-789', $result->externalId);
    }

    public function test_nytimes_handles_missing_multimedia(): void
    {
        $service = new NYTimesService();

        $apiArticle = [
            'headline' => [
                'main' => 'NYTimes Article',
            ],
            'abstract' => 'Abstract',
            'lead_paragraph' => 'Lead',
            'byline' => [
                'original' => 'Author',
            ],
            'web_url' => 'https://nytimes.com/article',
            'multimedia' => [],
            'pub_date' => '2024-01-15T10:30:00Z',
            'section_name' => 'World',
            '_id' => 'nyt-999',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertNull($result->imageUrl);
    }

    public function test_nytimes_handles_null_values(): void
    {
        $service = new NYTimesService();

        $apiArticle = [
            'headline' => [
                'main' => 'Title Only',
            ],
            'abstract' => null,
            'lead_paragraph' => null,
            'byline' => null,
            'web_url' => 'https://nytimes.com/article',
            'multimedia' => null,
            'pub_date' => '2024-01-15T10:30:00Z',
            'section_name' => null,
            '_id' => 'nyt-000',
        ];

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('transformArticle');
        $method->setAccessible(true);

        $result = $method->invoke($service, $apiArticle);

        $this->assertInstanceOf(ArticleData::class, $result);
        $this->assertNull($result->description);
        $this->assertNull($result->content);
        $this->assertNull($result->author);
        $this->assertNull($result->imageUrl);
        $this->assertNull($result->category);
    }

    public function test_article_data_to_array_conversion(): void
    {
        $articleData = new ArticleData(
            title: 'Test Title',
            description: 'Test Description',
            content: 'Test Content',
            author: 'Test Author',
            source: NewsSource::NEWSAPI->value,
            category: 'technology',
            url: 'https://example.com',
            imageUrl: 'https://example.com/image.jpg',
            externalId: 'ext-123',
            publishedAt: now()
        );

        $array = $articleData->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Test Title', $array['title']);
        $this->assertEquals('Test Description', $array['description']);
        $this->assertEquals('Test Content', $array['content']);
        $this->assertEquals('Test Author', $array['author']);
        $this->assertEquals('newsapi', $array['source']);
        $this->assertEquals('technology', $array['category']);
        $this->assertEquals('https://example.com', $array['url']);
        $this->assertEquals('https://example.com/image.jpg', $array['image_url']);
        $this->assertEquals('ext-123', $array['external_id']);
        $this->assertInstanceOf(\DateTimeInterface::class, $array['published_at']);
    }
}

