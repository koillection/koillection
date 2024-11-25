<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginatorFactory
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly int $paginationItemsPerPage = 15
    ) {
    }

    public function generate(int $totalItems, ?string $url = null, ?array $params = null, $queryParam = 'page'): Paginator
    {
        $url ??= $this->requestStack->getMainRequest()->getPathInfo();
        $params ??= $this->requestStack->getMainRequest()->query->all();
        $page = $params[$queryParam] ?? 1;

        unset($params[$queryParam]);

        if ($params !== []) {
            $url .= '?' . http_build_query($params);
        }

        return new Paginator($totalItems, $this->paginationItemsPerPage, (int) $page, $url);
    }

    public function getPaginationItemsPerPage(): int
    {
        return $this->paginationItemsPerPage;
    }
}
