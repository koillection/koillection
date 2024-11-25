<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Path;
use App\Enum\DatumTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class PathFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
        parent::__construct();
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->text(),
            'path' => self::faker()->text(),
            'position' => self::faker()->randomNumber(),
            'type' => DatumTypeEnum::TYPE_TEXT,
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
        return Path::class;
    }
}
