<?php

declare(strict_types=1);

namespace App\Tests\App\Visibility;

use App\Entity\User;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishlistFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class VisibilityProfileTest extends AppTestCase
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

    public function test_anonymous_cant_see_private_profile(): void
    {
        // Arrange
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    public function test_logged_user_cant_see_private_profile(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PRIVATE])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    public function test_anonymous_user_cant_see_internal_profile(): void
    {
        // Arrange
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_INTERNAL])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    public function test_logged_user_can_see_internal_profile(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_INTERNAL])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseIsSuccessful();
        }
    }

    public function test_anonymous_user_cant_see_public_profile(): void
    {
        // Arrange
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseIsSuccessful();
        }
    }

    public function test_logged_user_can_see_public_profile(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $owner = UserFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC])->_real();

        foreach ($this->getUrlsForOwner($owner) as $url) {
            // Act
            $this->client->request(Request::METHOD_GET, $url);

            // Assert
            $this->assertResponseIsSuccessful();
        }
    }

    private function getUrlsForOwner(User $owner): array
    {
        $username = $owner->getUsername();

        $collection = CollectionFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC, 'owner' => $owner])->_real();
        $collectionId = $collection->getId();

        $itemId = ItemFactory::createOne(['collection' => $collection, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC, 'owner' => $owner])->getId();
        $albumId = AlbumFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC, 'owner' => $owner])->getId();
        $wishlistId = WishlistFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC, 'owner' => $owner])->getId();
        $tagId = TagFactory::createOne(['visibility' => VisibilityEnum::VISIBILITY_PUBLIC, 'owner' => $owner])->getId();

        return [
            "/user/{$username}",
            "/user/{$username}/collections",
            "/user/{$username}/collections/{$collectionId}",
            "/user/{$username}/collections/{$collectionId}/items",
            "/user/{$username}/items/{$itemId}",
            "/user/{$username}/albums",
            "/user/{$username}/albums/{$albumId}",
            "/user/{$username}/wishlists",
            "/user/{$username}/wishlists/{$wishlistId}",
            "/user/{$username}/tags",
            "/user/{$username}/tags/{$tagId}",
            "/user/{$username}/statistics",
            "/user/{$username}/signatures",
        ];
    }
}
