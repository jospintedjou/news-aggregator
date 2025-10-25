<?php

namespace App\Services;

use App\DataTransferObjects\ArticleFilterData;
use App\Models\User;
use App\Repositories\ArticleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleService
{
    public function __construct(
        protected ArticleRepository $repository
    ) {}

    /**
     * Get paginated articles with filters
     *
     * @param ArticleFilterData $filters
     * @param User|null $user
     * @return LengthAwarePaginator
     */
    public function getPaginatedArticles(ArticleFilterData $filters, ?User $user = null): LengthAwarePaginator
    {
        if ($user) {
            return $this->repository->getPaginatedForUser($user, $filters->toArray(), $filters->perPage);
        }

        return $this->repository->getPaginated($filters->toArray(), $filters->perPage);
    }

    /**
     * Find an article by ID
     *
     * @param int $id
     * @return \App\Models\Article|null
     */
    public function findArticleById(int $id)
    {
        return $this->repository->findById($id);
    }

    /**
     * Get all available categories
     *
     * @return array
     */
    public function getAvailableCategories(): array
    {
        return $this->repository->getCategories();
    }

    /**
     * Get all available authors
     *
     * @return array
     */
    public function getAvailableAuthors(): array
    {
        return $this->repository->getAuthors();
    }
}
