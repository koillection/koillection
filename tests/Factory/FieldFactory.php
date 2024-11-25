<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Field;
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class FieldFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(),
            'type' => DatumTypeEnum::TYPE_TEXT,
            'position' => self::faker()->randomNumber(),
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC
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
        return Field::class;
    }
}
