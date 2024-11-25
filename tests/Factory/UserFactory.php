<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\User;
use App\Enum\DateFormatEnum;
use App\Enum\RoleEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class UserFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'username' => self::faker()->unique()->word(),
            'email' => self::faker()->unique()->email(),
            'plainPassword' => self::faker()->password(),
            'enabled' => true,
            'roles' => [RoleEnum::ROLE_USER],
            'currency' => self::faker()->currencyCode(),
            'locale' => 'en',
            'timezone' => self::faker()->timezone(),
            'dateFormat' => DateFormatEnum::FORMAT_HYPHEN_YMD,
            'diskSpaceAllowed' => 536870912,
            'visibility' => VisibilityEnum::VISIBILITY_PUBLIC,
            'theme' => ThemeEnum::THEME_BROWSER,
            'wishlistsFeatureEnabled' => true,
            'tagsFeatureEnabled' => true,
            'signsFeatureEnabled' => true,
            'albumsFeatureEnabled' => true,
            'loansFeatureEnabled' => true,
            'templatesFeatureEnabled' => true,
            'historyFeatureEnabled' => true,
            'statisticsFeatureEnabled' => true,
            'searchInDataByDefaultEnabled' => false,
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
        return User::class;
    }
}
