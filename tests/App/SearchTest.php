<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishlistFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SearchTest extends AppTestCase
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

    public function test_can_use_search(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $now = new \DateTimeImmutable();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'createdAt' => $now])->_real();
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);
        TagFactory::createOne(['label' => 'Frieren', 'owner' => $user, 'createdAt' => $now]);
        WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user, 'createdAt' => $now]);
        AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user, 'createdAt' => $now]);

        $collectionBerserk = CollectionFactory::createOne(['title' => 'Berserk', 'owner' => $user, 'createdAt' => $now])->_real();
        ItemFactory::createOne(['name' => 'Berserk #1', 'collection' => $collectionBerserk, 'owner' => $user, 'createdAt' => $now]);
        TagFactory::createOne(['label' => 'Berserk', 'owner' => $user, 'createdAt' => $now]);
        WishlistFactory::createOne(['name' => 'Wishlist Berserk', 'owner' => $user, 'createdAt' => $now]);
        AlbumFactory::createOne(['title' => 'Berserk collection', 'owner' => $user, 'createdAt' => $now]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/search');
        $crawler = $this->client->submitForm('Submit', [
            'search[term]' => 'frie',
            'search[createdAt]' => $now->format('Y-m-d'),
        ], 'GET');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Items (1)', $crawler->filter('.tab')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.collection-item'));

        $this->assertSame('Collections (1)', $crawler->filter('.tab')->eq(1)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(0)->filter('.collection-element'));

        $this->assertSame('Tags (1)', $crawler->filter('.tab')->eq(2)->text());
        $this->assertCount(1, $crawler->filter('.list-element'));

        $this->assertSame('Wishlists (1)', $crawler->filter('.tab')->eq(3)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(1)->filter('.collection-element'));

        $this->assertSame('Albums (1)', $crawler->filter('.tab')->eq(4)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(2)->filter('.collection-element'));
    }

    public function test_can_use_search_without_data(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $now = new \DateTimeImmutable();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'createdAt' => $now])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'ISBN', 'value' => '9791032710838']);
        ItemFactory::createOne(['name' => 'Frieren 9791032710838', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);

        // Act
        $this->client->request(Request::METHOD_GET, '/search');
        $crawler = $this->client->submitForm('Submit', [
            'search[term]' => '9791032710838',
            'search[createdAt]' => $now->format('Y-m-d'),
        ], 'GET');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Items (1)', $crawler->filter('.tab')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.collection-item'));
    }

    public function test_can_use_search_with_data(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $now = new \DateTimeImmutable();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'createdAt' => $now])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'ISBN', 'value' => '9791032710838']);
        ItemFactory::createOne(['name' => 'Frieren 9791032710838', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);

        // Act
        $this->client->request(Request::METHOD_GET, '/search');
        $crawler = $this->client->submitForm('Submit', [
            'search[term]' => '9791032710838',
            'search[createdAt]' => $now->format('Y-m-d'),
            'search[searchInData]' => 1,
        ], 'GET');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Items (2)', $crawler->filter('.tab')->eq(0)->text());
        $this->assertCount(2, $crawler->filter('.collection-item'));
    }

    public function test_search_need_at_least_one_field(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/search');

        $crawler = $this->client->submitForm('Submit', [
        ], 'GET');

        // Assert
        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Please fill at least one of the fields', $crawler->filter('.error-helper div')->eq(0)->text());
    }

    public function test_anonymous_user_search_public(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user])->_real();
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user]);
        TagFactory::createOne(['label' => 'Frieren', 'owner' => $user]);
        WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user]);
        AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search');
        $crawler = $this->client->submitForm('Submit', [
            'search[term]' => 'fri',
        ], 'GET');

        // Assert
        $this->assertResponseIsSuccessful();

        $this->assertSame('Items (1)', $crawler->filter('.tab')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.collection-item'));

        $this->assertSame('Collections (1)', $crawler->filter('.tab')->eq(1)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(0)->filter('.collection-element'));

        $this->assertSame('Tags (1)', $crawler->filter('.tab')->eq(2)->text());
        $this->assertCount(1, $crawler->filter('.list-element'));

        $this->assertSame('Wishlists (1)', $crawler->filter('.tab')->eq(3)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(1)->filter('.collection-element'));

        $this->assertSame('Albums (1)', $crawler->filter('.tab')->eq(4)->text());
        $this->assertCount(1, $crawler->filter('.grid-container-collections')->eq(2)->filter('.collection-element'));
    }

    public function test_anonymous_user_search_entities_private(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        TagFactory::createOne(['label' => 'Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search');
        $crawler = $this->client->submitForm('Submit', [
            'search[term]' => 'fri',
        ], 'GET');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Items (0)', $crawler->filter('.tab')->eq(0)->text());
        $this->assertSame('Collections (0)', $crawler->filter('.tab')->eq(1)->text());
        $this->assertSame('Tags (0)', $crawler->filter('.tab')->eq(2)->text());
        $this->assertSame('Wishlists (0)', $crawler->filter('.tab')->eq(3)->text());
        $this->assertSame('Albums (0)', $crawler->filter('.tab')->eq(4)->text());
    }

    public function test_anonymous_user_private(): void
    {
        // Arrange
        $user = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search');

        // Assert
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function test_can_use_search_autocomplete(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user]);
        $tag = TagFactory::createOne(['label' => 'Frieren', 'owner' => $user]);
        $wishlist = WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user]);
        $album = AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/search/autocomplete/fri');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(5, $content['totalResultsCounter']);
        $this->assertContains(['label' => 'Frieren', 'type' => 'collection', 'url' => '/collections/' . $collection->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren #1', 'type' => 'item', 'url' => '/items/' . $item->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren', 'type' => 'tag', 'url' => '/tags/' . $tag->getId()], $content['results']);
        $this->assertContains(['label' => 'Wishlist Frieren', 'type' => 'wishlist', 'url' => '/wishlists/' . $wishlist->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren collection', 'type' => 'album', 'url' => '/albums/' . $album->getId()], $content['results']);
    }

    public function test_can_use_search_autocomplete_without_data(): void
    {
        // Arrange
        $user = UserFactory::createOne(['searchInDataByDefaultEnabled' => 0])->_real();
        $this->client->loginUser($user);
        $now = new \DateTimeImmutable();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'createdAt' => $now])->_real();
        $item1 = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'ISBN', 'value' => '9791032710838']);
        $item2 = ItemFactory::createOne(['name' => 'Frieren 9791032710838', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);

        // Act
        $this->client->request(Request::METHOD_GET, '/search/autocomplete/9791032710838');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $content['totalResultsCounter']);
        $this->assertContains(['label' => 'Frieren 9791032710838', 'type' => 'item', 'url' => '/items/' . $item2->getId()], $content['results']);
    }

    public function test_can_use_search_autocomplete_with_data(): void
    {
        // Arrange
        $user = UserFactory::createOne(['searchInDataByDefaultEnabled' => 1])->_real();
        $this->client->loginUser($user);
        $now = new \DateTimeImmutable();
        $collectionFrieren = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'createdAt' => $now])->_real();
        $item1 = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'ISBN', 'value' => '9791032710838']);
        $item2 = ItemFactory::createOne(['name' => 'Frieren 9791032710838', 'collection' => $collectionFrieren, 'owner' => $user, 'createdAt' => $now]);

        // Act
        $this->client->request(Request::METHOD_GET, '/search/autocomplete/9791032710838');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(2, $content['totalResultsCounter']);
        $this->assertContains(['label' => 'Frieren #1', 'type' => 'item', 'url' => '/items/' . $item1->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren 9791032710838', 'type' => 'item', 'url' => '/items/' . $item2->getId()], $content['results']);
    }

    public function test_anonymous_search_autocomplete_entities_public(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user]);
        $tag = TagFactory::createOne(['label' => 'Frieren', 'owner' => $user]);
        $wishlist = WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user]);
        $album = AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search/autocomplete/fri');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(5, $content['totalResultsCounter']);
        $this->assertContains(['label' => 'Frieren', 'type' => 'collection', 'url' => '/collections/' . $collection->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren #1', 'type' => 'item', 'url' => '/items/' . $item->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren', 'type' => 'tag', 'url' => '/tags/' . $tag->getId()], $content['results']);
        $this->assertContains(['label' => 'Wishlist Frieren', 'type' => 'wishlist', 'url' => '/wishlists/' . $wishlist->getId()], $content['results']);
        $this->assertContains(['label' => 'Frieren collection', 'type' => 'album', 'url' => '/albums/' . $album->getId()], $content['results']);
    }

    public function test_anonymous_search_autocomplete_entities_private(): void
    {
        // Arrange
        $user = UserFactory::createOne(['searchInDataByDefaultEnabled' => 1])->_real();
        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Origin', 'value' => 'Frieren']);
        TagFactory::createOne(['label' => 'Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        WishlistFactory::createOne(['name' => 'Wishlist Frieren', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        AlbumFactory::createOne(['title' => 'Frieren collection', 'owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search/autocomplete/fri');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(0, $content['totalResultsCounter']);
    }

    public function test_anonymous_search_autocomplete_user_private(): void
    {
        // Arrange
        $user = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/user/' . $user->getUsername() . '/search/autocomplete');

        // Assert
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
}
