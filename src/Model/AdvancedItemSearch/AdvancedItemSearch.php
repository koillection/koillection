<?php

declare(strict_types=1);

namespace App\Model\AdvancedItemSearch;

use App\Enum\DisplayModeEnum;

class AdvancedItemSearch
{
    private string $displayMode = DisplayModeEnum::DISPLAY_MODE_GRID;

    private array $blocks = [];

    public function __construct()
    {
        $filter = new Filter();
        $block = new Block();

        $block->setFilters([$filter]);
        $this->setBlocks([$block]);
    }

    public function getDisplayMode(): string
    {
        return $this->displayMode;
    }

    public function setDisplayMode(string $displayMode): AdvancedItemSearch
    {
        $this->displayMode = $displayMode;

        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function setBlocks(array $blocks): AdvancedItemSearch
    {
        $this->blocks = $blocks;

        return $this;
    }
}
