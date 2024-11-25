<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Enum\DateFormatEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SettingsTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    public function test_can_edit_settings(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/settings');

        $this->client->submitForm('Submit', [
            'settings[locale]' => 'fr',
            'settings[currency]' => 'EUR',
            'settings[timezone]' => 'Europe/Paris',
            'settings[dateFormat]' => DateFormatEnum::FORMAT_SLASH_DMY,
            'settings[visibility]' => VisibilityEnum::VISIBILITY_INTERNAL,
            'settings[theme]' => ThemeEnum::THEME_BROWSER,
            'settings[wishlistsFeatureEnabled]' => 1,
            'settings[tagsFeatureEnabled]' => 1,
            'settings[signsFeatureEnabled]' => 1,
            'settings[albumsFeatureEnabled]' => 1,
            'settings[loansFeatureEnabled]' => 1,
            'settings[templatesFeatureEnabled]' => 1,
            'settings[historyFeatureEnabled]' => 1,
            'settings[statisticsFeatureEnabled]' => 1,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        // $this->assertSame('Paramètres', $crawler->filter('h1')->text());
        UserFactory::assert()->exists([
            'id' => $user->getId(),
            'locale' => 'fr',
            'currency' => 'EUR',
            'timezone' => 'Europe/Paris',
            'dateFormat' => DateFormatEnum::FORMAT_SLASH_DMY,
            'visibility' => VisibilityEnum::VISIBILITY_INTERNAL,
            'wishlistsFeatureEnabled' => true,
            'tagsFeatureEnabled' => true,
            'signsFeatureEnabled' => true,
            'albumsFeatureEnabled' => true,
            'loansFeatureEnabled' => true,
            'templatesFeatureEnabled' => true,
            'historyFeatureEnabled' => true,
            'statisticsFeatureEnabled' => true,
        ]);
    }
}
