<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\DisplayConfiguration;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class DisplayConfigurationFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'displayMode' => self::faker()->text(),
            'sortingDirection' => self::faker()->text(),
            'showVisibility' => self::faker()->boolean(),
            'showActions' => self::faker()->boolean(),
            'showNumberOfChildren' => self::faker()->boolean(),
            'showNumberOfItems' => self::faker()->boolean(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }

    public static function class(): string
    {
        return DisplayConfiguration::class;
    }
}
