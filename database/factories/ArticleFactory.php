<?php

namespace Database\Factories;

use App\Enums\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(2),
            'content' => fake()->paragraphs(5, true),
            'author' => fake()->name(),
            'source' => fake()->randomElement(NewsSource::cases()),
            'category' => fake()->randomElement(['technology', 'business', 'sports', 'entertainment', 'health', 'science']),
            'url' => fake()->url(),
            'image_url' => fake()->imageUrl(),
            'external_id' => fake()->uuid(),
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the article is from NewsAPI.
     */
    public function newsapi(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => NewsSource::NEWSAPI,
        ]);
    }

    /**
     * Indicate that the article is from The Guardian.
     */
    public function guardian(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => NewsSource::GUARDIAN,
        ]);
    }

    /**
     * Indicate that the article is from NY Times.
     */
    public function nytimes(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => NewsSource::NYTIMES,
        ]);
    }

    /**
     * Indicate that the article is in a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the article is by a specific author.
     */
    public function byAuthor(string $author): static
    {
        return $this->state(fn (array $attributes) => [
            'author' => $author,
        ]);
    }

    /**
     * Indicate that the article was published on a specific date.
     */
    public function publishedAt(\DateTimeInterface $date): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => $date,
        ]);
    }
}
