<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\DisplayConfiguration;
use App\Service\ArraySorter;
use Twig\Extension\RuntimeExtensionInterface;

class ArrayRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ArraySorter $arraySorter
    ) {
    }

    public function naturalSorting(
        iterable $array, ?DisplayConfiguration $displayConfiguration = null
    ): array {
        return $this->arraySorter->sort($array, $displayConfiguration);
    }
}
