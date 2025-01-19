<?php

declare(strict_types=1);

namespace App\Model\AdvancedItemSearch;

class Block
{
    private ?string $condition = null;

    private array $filters = [];

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): Block
    {
        $this->condition = $condition;

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): Block
    {
        $this->filters = $filters;

        return $this;
    }
}
