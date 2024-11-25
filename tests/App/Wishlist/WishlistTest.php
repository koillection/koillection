<?php

declare(strict_types=1);

namespace App\Tests\App\Wishlist;

use App\Enum\DisplayModeEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishFactory;
use App\Tests\Factory\WishlistFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishlistTest extends AppTestCase
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

    public function test_can_get_wishlist_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        WishlistFactory::createMany(3, ['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/wishlists');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Wishlists', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    public function test_can_edit_wishlist_index(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        WishlistFactory::createMany(3, ['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/wishlists/edit');
        $crawler = $this->client->submitForm('Submit', [
            'display_configuration[displayMode]' => DisplayModeEnum::DISPLAY_MODE_LIST,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Wishlists', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.list-element'));
    }

    public function test_can_get_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $wishlist = WishlistFactory::createOne(['owner' => $user]);
        WishlistFactory::createMany(3, ['owner' => $user, 'parent' => $wishlist]);
        WishFactory::createMany(3, ['owner' => $user, 'wishlist' => $wishlist]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/wishlists/' . $wishlist->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($wishlist->getName(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.list-element'));
    }

    public function test_can_get_wishlist_with_list_view(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        $wishlist = WishlistFactory::createOne(['owner' => $user]);
        $wishlist->getChildrenDisplayConfiguration()->setDisplayMode(DisplayModeEnum::DISPLAY_MODE_LIST);
        $wishlist->_save();

        WishlistFactory::createMany(3, ['owner' => $user, 'parent' => $wishlist]);
        WishFactory::createMany(3, ['owner' => $user, 'wishlist' => $wishlist]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/wishlists/' . $wishlist->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($wishlist->getName(), $crawler->filter('h1')->text());

        $this->assertCount(3, $crawler->filter('.children-table tbody tr'));
        $this->assertCount(3, $crawler->filter('.items-table tbody tr'));
    }

    public function test_can_post_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $parent = WishlistFactory::createOne(['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/wishlists/add?parent=' . $parent->getId());

        $crawler = $this->client->submitForm('Submit', [
            'wishlist[name]' => 'Books',
            'wishlist[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC
        ]);

        // Assert
        $this->assertSame('Books', $crawler->filter('h1')->text());
    }

    public function test_can_edit_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $wishlist = WishlistFactory::createOne(['owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);
        $imagePath = $wishlist->getImage();

        // Act
        $this->client->request(Request::METHOD_GET, '/wishlists/' . $wishlist->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'wishlist[name]' => 'Video games',
            'wishlist[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC
        ]);

        // Assert
        $this->assertSame('Video games', $crawler->filter('h1')->text());
        $this->assertFileExists($imagePath);
    }

    public function test_can_delete_wishlist_image(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = WishlistFactory::createOne(['name' => 'Books', 'owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);
        $oldImagePath = $album->getImage();

        // Act
        $this->client->request(Request::METHOD_GET, '/wishlists/' . $album->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'wishlist[deleteImage]' => true,
        ]);

        // Assert
        $this->assertSame('B', $crawler->filter('.collection-header')->filter('.thumbnail')->text());
        $this->assertFileDoesNotExist($oldImagePath);
    }

    public function test_can_delete_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $wishlist = WishlistFactory::createOne(['owner' => $user]);
        $childWishlist = WishlistFactory::createOne(['parent' => $wishlist, 'owner' => $user]);
        $otherWishlist = WishlistFactory::createOne(['owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlist, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $childWishlist, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $otherWishlist, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/wishlists/' . $wishlist->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/wishlists/' . $wishlist->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        WishlistFactory::assert()->count(1);
        WishFactory::assert()->count(3);
    }
}
