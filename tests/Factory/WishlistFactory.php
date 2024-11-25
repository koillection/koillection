<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Wishlist;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class WishlistFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'seenCounter' => self::faker()->randomNumber(),
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC,
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'cachedValues' => [
                'counters' => [
                    'publicCounters' => [
                        'children' => 0,
                        'wishes' => 0,
                    ],
                    'internalCounters' => [
                        'children' => 0,
                        'wishes' => 0,
                    ],
                    'privateCounters' => [
                        'children' => 0,
                        'wishes' => 0,
                    ]
                ],
            ]
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(static function (Wishlist $wishlist): void {
                $wishlist->getChildrenDisplayConfiguration()->setOwner($wishlist->getOwner());
            })
        ;
    }

    #[\Override]
    public static function class(): string
    {
        return Wishlist::class;
    }
}
