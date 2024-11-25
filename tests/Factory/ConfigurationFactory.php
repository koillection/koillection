<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Configuration;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class ConfigurationFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
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
        return Configuration::class;
    }
}
