<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Repository\CollectionRepository;
use App\Service\InventoryHandler;
use Symfony\Component\Form\DataTransformerInterface;

class StringToInventoryContentTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly InventoryHandler $inventoryHandler,
        private readonly CollectionRepository $collectionRepository
    ) {
    }

    #[\Override]
    public function transform($content): string
    {
        return '';
    }

    #[\Override]
    public function reverseTransform($string): array
    {
        if (null === $string) {
            return [];
        }

        $ids = explode(',', $string);
        $collections = $this->collectionRepository->findAllWithItems();

        return $this->inventoryHandler->buildInventory($collections, $ids);
    }
}
