<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Scraper;
use App\Enum\ScraperTypeEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class ScraperFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'name' => self::faker()->text(),
            'type' => ScraperTypeEnum::TYPE_ITEM
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
        return Scraper::class;
    }
}
