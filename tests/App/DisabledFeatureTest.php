<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Entity\User;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\ScraperFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TemplateFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishlistFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DisabledFeatureTest extends AppTestCase
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

    public function test_disabled_features_not_in_menu(): void
    {
        // Arrange
        $user = UserFactory::createOne([
            'wishlistsFeatureEnabled' => false,
            'tagsFeatureEnabled' => false,
            'signsFeatureEnabled' => false,
            'albumsFeatureEnabled' => false,
            'loansFeatureEnabled' => false,
            'templatesFeatureEnabled' => false,
            'historyFeatureEnabled' => false,
            'statisticsFeatureEnabled' => false,
        ])->_real();
        $this->client->loginUser($user);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        // Assert
        $this->assertStringContainsString('Collections', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Choice lists', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Wishlists', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Tags', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Signatures', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Albums', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Loans', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Template', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('History', $crawler->filter('.nav-sidebar')->text());
        $this->assertStringNotContainsString('Statistics', $crawler->filter('.nav-sidebar')->text());
    }

    public function test_owner_cant_access_disabled_features(): void
    {
        // Arrange
        $user = UserFactory::createOne([
            'wishlistsFeatureEnabled' => false,
            'tagsFeatureEnabled' => false,
            'signsFeatureEnabled' => false,
            'albumsFeatureEnabled' => false,
            'loansFeatureEnabled' => false,
            'templatesFeatureEnabled' => false,
            'historyFeatureEnabled' => false,
            'statisticsFeatureEnabled' => false,
            'scrapingFeatureEnabled' => false,
        ])->_real();
        $this->client->loginUser($user);

        foreach ($this->getUrls($user) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    private function getUrls(User $owner): array
    {
        $albumId = AlbumFactory::createOne(['owner' => $owner])->getId();
        $wishlistId = WishlistFactory::createOne(['owner' => $owner])->getId();
        $tagId = TagFactory::createOne(['owner' => $owner])->getId();
        $templateId = TemplateFactory::createOne(['owner' => $owner])->getId();
        $choiceListId = ChoiceListFactory::createOne(['owner' => $owner])->getId();
        $scraperId = ScraperFactory::createOne(['owner' => $owner])->getId();

        return [
            '/albums',
            "/albums/{$albumId}",
            '/wishlists',
            "/wishlists/{$wishlistId}",
            '/tags',
            "/tags/{$tagId}",
            '/templates',
            "/templates/{$templateId}",
            '/choice-lists',
            "/choice-lists/{$choiceListId}",
            '/scrapers',
            "/scrapers/{$scraperId}",
            '/loans',
            '/signatures',
            '/statistics',
            '/signatures',
        ];
    }
}
