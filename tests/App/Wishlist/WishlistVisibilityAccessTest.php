<?php

declare(strict_types=1);

namespace App\Tests\App\Wishlist;

use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishFactory;
use App\Tests\Factory\WishlistFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishlistVisibilityAccessTest extends AppTestCase
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

    public function test_shared_wishlists_list_with_anonymous(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Wishlists', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('.collection-element'));
    }

    public function test_shared_wishlists_list_with_other_user_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Wishlists', $crawler->filter('h1')->text());
        $this->assertCount(2, $crawler->filter('.collection-element'));
    }

    public function test_shared_wishlists_list_with_owner_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/wishlists");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Wishlists', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', false])]
    #[TestWith(['private', false])]
    public function test_shared_get_wishlist_with_anonymous(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlist = WishlistFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists"); //Don't know why it's needed, it seems like $wishlist isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists/{$wishlist->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($wishlist->getName(), $crawler->filter('h1')->text());
            $this->assertCount(1, $crawler->filter('.collection-element'));
            $this->assertCount(1, $crawler->filter('.list-element'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', true])]
    #[TestWith(['private', false])]
    public function test_shared_get_wishlist_with_other_logged_user(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlist = WishlistFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists");
         //Don't know why it's needed, it seems like $wishlist isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/wishlists/{$wishlist->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($wishlist->getName(), $crawler->filter('h1')->text());
            $this->assertCount(2, $crawler->filter('.collection-element'));
            $this->assertCount(2, $crawler->filter('.list-element'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public'])]
    #[TestWith(['internal'])]
    #[TestWith(['private'])]
    public function test_get_wishlist_with_owner_logged(string $visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlist = WishlistFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishlistFactory::createOne(['owner' => $user, 'parent' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlist, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/wishlists/{$wishlist->getId()}");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($wishlist->getName(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.list-element'));
    }
}
