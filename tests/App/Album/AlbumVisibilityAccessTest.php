<?php

declare(strict_types=1);

namespace App\Tests\App\Album;

use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AlbumVisibilityAccessTest extends AppTestCase
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

    public function test_shared_albums_list_with_anonymous(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Albums', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('.collection-element'));
    }

    public function test_shared_albums_list_with_user_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Albums', $crawler->filter('h1')->text());
        $this->assertCount(2, $crawler->filter('.collection-element'));
    }

    public function test_albums_list_with_owner_logged(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/albums");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Albums', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', false])]
    #[TestWith(['private', false])]
    public function test_shared_get_album_with_anonymous(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $album = AlbumFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums"); //Don't know why it's needed, it seems like $album isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums/{$album->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($album->getTitle(), $crawler->filter('h1')->text());
            $this->assertCount(1, $crawler->filter('.collection-element'));
            $this->assertCount(1, $crawler->filter('.collection-item'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public', true])]
    #[TestWith(['internal', true])]
    #[TestWith(['private', false])]
    public function test_shared_get_album_with_other_user_logged(string $visibility, bool $shouldSucceed): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $album = AlbumFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $otherUser = UserFactory::createOne()->_real();
        $this->client->loginUser($otherUser);
        $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums");
         //Don't know why it's needed, it seems like $album isn't properly initialized, maybe from some cache
        $crawler = $this->client->request(Request::METHOD_GET, "/user/{$user->getUsername()}/albums/{$album->getId()}");

        // Assert
        if ($shouldSucceed) {
            $this->assertResponseIsSuccessful();
            $this->assertEquals($album->getTitle(), $crawler->filter('h1')->text());
            $this->assertCount(2, $crawler->filter('.collection-element'));
            $this->assertCount(2, $crawler->filter('.collection-item'));
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[TestWith(['public'])]
    #[TestWith(['internal'])]
    #[TestWith(['private'])]
    public function test_get_album_with_owner_logged(string $visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $album = AlbumFactory::createOne(['owner' => $user, 'visibility' => $visibility]);

        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        AlbumFactory::createOne(['owner' => $user, 'parent' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PUBLIC]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_INTERNAL]);
        PhotoFactory::createOne(['owner' => $user, 'album' => $album, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);

        // Act
        $this->client->loginUser($user);
        $crawler = $this->client->request(Request::METHOD_GET, "/albums/{$album->getId()}");

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($album->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.collection-item'));
    }
}
