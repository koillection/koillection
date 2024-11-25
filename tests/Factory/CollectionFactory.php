<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Collection;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class CollectionFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'parent' => null,
            'title' => self::faker()->word(),
            'seenCounter' => self::faker()->randomNumber(),
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC,
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'cachedValues' => [
                'counters' => [
                    'publicCounters' => [
                        'children' => 0,
                        'items' => 0,
                    ],
                    'internalCounters' => [
                        'children' => 0,
                        'items' => 0,
                    ],
                    'privateCounters' => [
                        'children' => 0,
                        'items' => 0,
                    ]
                ],
                'prices' => [
                    'publicPrices' => [],
                    'internalPrices' => [],
                    'privatePrices' => []
                ]
            ]
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(static function (Collection $collection): void {
                $collection->getChildrenDisplayConfiguration()->setOwner($collection->getOwner());
                $collection->getItemsDisplayConfiguration()->setOwner($collection->getOwner());
            })
        ;
    }

    #[\Override]
    public static function class(): string
    {
        return Collection::class;
    }
}
