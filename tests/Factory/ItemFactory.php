<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Item;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class ItemFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'quantity' => 1,
            'seenCounter' => self::faker()->randomNumber(),
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC,
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }

    #[\Override]
    public static function class(): string
    {
        return Item::class;
    }
}
