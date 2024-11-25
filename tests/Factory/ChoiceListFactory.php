<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\ChoiceList;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class ChoiceListFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->word(),
            'choices' => [],
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
        return ChoiceList::class;
    }
}
