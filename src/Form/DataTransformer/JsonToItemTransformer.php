<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Repository\ItemRepository;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\DataTransformerInterface;

class JsonToItemTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly Packages $assetManager
    ) {
    }

    #[\Override]
    public function transform($items): string|bool
    {
        $array = [];
        foreach ($items as $item) {
            $array[] = [
                'id' => $item->getId(),
                'text' => $item->getName(),
                'image' => $item->getImageSmallThumbnail() ? $this->assetManager->getUrl($item->getImageSmallThumbnail()) : null,
            ];
        }

        return json_encode($array);
    }

    #[\Override]
    public function reverseTransform($json): array
    {
        $items = [];
        foreach (json_decode($json, true) as $id) {
            $item = $this->itemRepository->find($id);

            if (!\in_array($item, $items, false)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
