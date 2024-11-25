<?php

declare(strict_types=1);

namespace App\Tests\App\Collection;

use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CollectionVisibilityAccessTest extends AppTestCase
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

    public function test_shared_collections_list_with_anonymous(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('.collection-element'));
    }

    public function test_shared_collections_list_with_other_user_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
        $this->assertCount(2, $crawler->filter('.collection-element'));
    }

    public function test_shared_collections_list_with_owner_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/collections");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', false])]
    #[TestWith(['private', false])]
    public function test_shared_get_collection_with_anonymous(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections"); //Don't know why it's needed, it seems like $collection isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections/{$collection->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($collection->getTitle(), $crawler->filter('h1')->text());
            $this->assertCount(1, $crawler->filter('.collection-element'));
            $this->assertCount(1, $crawler->filter('.collection-item'));
            $this->assertCount(1, $crawler->filter('.datum-row'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', true])]
    #[TestWith(['private', false])]
    public function test_shared_get_collection_with_other_user_logged(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections");
         //Don't know why it's needed, it seems like $collection isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/collections/{$collection->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($collection->getTitle(), $crawler->filter('h1')->text());
            $this->assertCount(2, $crawler->filter('.collection-element'));
            $this->assertCount(2, $crawler->filter('.collection-item'));
            $this->assertCount(2, $crawler->filter('.datum-row'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public'])]
    #[TestWith(['internal'])]
    #[TestWith(['private'])]
    public function test_get_collection_with_owner_logged(string $visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        CollectionFactory::createOne(['owner' => $user, 'parent' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/collections/{$collection->getId()}");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($collection->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.collection-item'));
        $this->assertCount(3, $crawler->filter('.datum-row'));
    }
}
