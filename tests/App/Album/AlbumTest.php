<?php

declare(strict_types=1);

namespace App\Tests\App\Album;

use App\Enum\DisplayModeEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AlbumTest extends AppTestCase
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

    public function test_can_get_album_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        AlbumFactory::createMany(3, ['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/albums');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Albums', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    public function test_can_get_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = AlbumFactory::createOne(['owner' => $user]);
        AlbumFactory::createMany(3, ['owner' => $user, 'parent' => $album]);
        PhotoFactory::createMany(3, ['owner' => $user, 'album' => $album]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/albums/' . $album->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($album->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.collection-item'));
    }

    public function test_can_get_album_with_list_view(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = AlbumFactory::createOne(['owner' => $user]);
        $album->getChildrenDisplayConfiguration()->setDisplayMode(DisplayModeEnum::DISPLAY_MODE_LIST);
        $album->_save();
        $album->getPhotosDisplayConfiguration()->setDisplayMode(DisplayModeEnum::DISPLAY_MODE_LIST);
        $album->_save();

        AlbumFactory::createMany(3, ['owner' => $user, 'parent' => $album]);
        PhotoFactory::createMany(3, ['owner' => $user, 'album' => $album]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/albums/' . $album->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($album->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.children-table tbody tr'));
        $this->assertCount(3, $crawler->filter('.items-table tbody tr'));
    }

    public function test_can_post_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $parent = AlbumFactory::createOne(['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/albums/add?parent=' . $parent->getId());
        $crawler = $this->client->submitForm('Submit', [
            'album[title]' => 'Home album',
            'album[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Home album', $crawler->filter('h1')->text());
    }

    public function test_can_edit_album_index(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        AlbumFactory::createMany(3, ['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/albums/edit');
        $crawler = $this->client->submitForm('Submit', [
            'display_configuration[displayMode]' => DisplayModeEnum::DISPLAY_MODE_LIST,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Albums', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.list-element'));
    }

    public function test_can_edit_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = AlbumFactory::createOne(['owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);
        $imagePath = $album->getImage();

        // Act
        $this->client->request(Request::METHOD_GET, '/albums/' . $album->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'album[title]' => 'Other album',
            'album[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC
        ]);

        // Assert
        $this->assertSame('Other album', $crawler->filter('h1')->text());
        $this->assertFileExists($imagePath);
    }

    public function test_can_delete_album_image(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = AlbumFactory::createOne(['title' => 'Home', 'owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);
        $oldAlbumImagePath = $album->getImage();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/albums/' . $album->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'album[deleteImage]' => true,
        ]);

        // Assert
        $this->assertSame('H', $crawler->filter('.collection-header')->filter('.thumbnail')->text());
        $this->assertFileDoesNotExist($oldAlbumImagePath);
    }

    public function test_can_delete_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $album = AlbumFactory::createOne(['owner' => $user]);
        $childAlbum = AlbumFactory::createOne(['parent' => $album, 'owner' => $user]);
        $otherAlbum = AlbumFactory::createOne(['owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $album, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $childAlbum, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $otherAlbum, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/albums/' . $album->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/albums/' . $album->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        AlbumFactory::assert()->count(1);
        PhotoFactory::assert()->count(3);
    }
}
