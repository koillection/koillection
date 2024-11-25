<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Datum;
use App\Enum\DatumTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class DatumFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'label' => self::faker()->word(),
            'type' => DatumTypeEnum::TYPE_TEXT,
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
        return Datum::class;
    }
}
