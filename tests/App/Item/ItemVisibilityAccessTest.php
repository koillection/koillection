<?php

declare(strict_types=1);

namespace App\Tests\App\Item;

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

class ItemVisibilityAccessTest extends AppTestCase
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

    #[TestWith(['public', true])]
    #[TestWith(['internal', false])]
    #[TestWith(['private', false])]
    public function test_shared_get_item_with_anonymous(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        $item = ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => $visibility]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/items/{$item->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($item->getName(), $crawler->filter('h1')->text());
            $this->assertCount(1, $crawler->filter('.datum-row'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', true])]
    #[TestWith(['private', false])]
    public function test_shared_get_item_with_other_user_logged(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        $item = ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => $visibility]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/items/{$item->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($item->getName(), $crawler->filter('h1')->text());
            $this->assertCount(2, $crawler->filter('.datum-row'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public'])]
    #[TestWith(['internal'])]
    #[TestWith(['private'])]
    public function test_shared_get_item_with_owner_logged(string $visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        $item = ItemFactory::createOne(['owner' => $user, 'collection' => $collection, 'visibility' => $visibility]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/items/{$item->getId()}");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($item->getName(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.datum-row'));
    }
}
