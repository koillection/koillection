<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Inventory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class InventoryFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'content' => [],
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
        return Inventory::class;
    }
}
