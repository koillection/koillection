<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Album;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class AlbumFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->word(),
            'seenCounter' => self::faker()->randomNumber(),
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC,
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'cachedValues' => [
                'counters' => [
                    'publicCounters' => [
                        'children' => 0,
                        'photos' => 0,
                    ],
                    'internalCounters' => [
                        'children' => 0,
                        'photos' => 0,
                    ],
                    'privateCounters' => [
                        'children' => 0,
                        'photos' => 0,
                    ]
                ],
            ]
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(static function (Album $album): void {
                $album->getChildrenDisplayConfiguration()->setOwner($album->getOwner());
                $album->getPhotosDisplayConfiguration()->setOwner($album->getOwner());
            })
        ;
    }

    public static function class(): string
    {
        return Album::class;
    }
}
