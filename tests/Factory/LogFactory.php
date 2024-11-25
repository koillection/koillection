<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Log;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class LogFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'loggedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'objectId' => self::faker()->uuid(),
            'objectLabel' => self::faker()->word(),
            'objectClass' => self::faker()->word(),
            'objectDeleted' => self::faker()->boolean(),
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
        return Log::class;
    }
}
