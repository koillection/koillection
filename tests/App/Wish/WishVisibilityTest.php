<?php

declare(strict_types=1);

namespace App\Tests\App\Wish;

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

class WishVisibilityTest extends AppTestCase
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

    #[TestWith(['public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'private'])]
    #[TestWith(['public', 'internal', 'internal'])]

    #[TestWith(['internal', 'public', 'internal'])]
    #[TestWith(['internal', 'private', 'private'])]
    #[TestWith(['internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private'])]
    #[TestWith(['private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private'])]
    public function test_visibility_add_wish(string $wishlist1Visibility, string $wishlist2Visibility, string $wishFinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user, 'visibility' => $wishlist1Visibility]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user, 'visibility' => $wishlist2Visibility]);

        // Act
        $wish = WishFactory::createOne(['owner' => $user, 'wishlist' => $wishlistLevel2]);

        // Assert
        WishFactory::assert()->exists(['id' => $wish->getId(), 'finalVisibility' => $wishFinalVisibility]);
    }


    #[TestWith(['public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'private'])]
    #[TestWith(['public', 'internal', 'internal'])]

    #[TestWith(['internal', 'public', 'internal'])]
    #[TestWith(['internal', 'private', 'private'])]
    #[TestWith(['internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private'])]
    #[TestWith(['private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private'])]
    public function test_visibility_change_wish_wishlist(string $wishlist1Visibility, string $wishlist2Visibility, string $wishFinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $oldWishlist = WishlistFactory::createOne(['owner' => $user]);
        $wish = WishFactory::createOne(['wishlist' => $oldWishlist, 'owner' => $user]);

        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user, 'visibility' => $wishlist1Visibility]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user, 'visibility' => $wishlist2Visibility]);

        // Act
        $wish->setWishlist($wishlistLevel2->_real());
        $wish->_save();

        // Assert
        WishFactory::assert()->exists(['id' => $wish->getId(), 'finalVisibility' => $wishFinalVisibility]);
    }

    public function test_wishlists_list_with_internal(): void
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

    public function test_wishlists_list_with_anonymous(): void
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

    #[TestWith(['public', true])]
    #[TestWith(['internal', false])]
    #[TestWith(['private', false])]
    public function test_get_wishlist_with_anonymous(string $visibility, bool $shouldSucceed): void
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
    public function test_get_wishlist_with_internal(string $visibility, bool $shouldSucceed): void
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
}
