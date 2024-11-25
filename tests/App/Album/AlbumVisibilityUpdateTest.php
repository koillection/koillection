<?php

declare(strict_types=1);

namespace App\Tests\App\Album;

use App\Entity\Album;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AlbumVisibilityUpdateTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    #[\Override]
    protected function setUp(): void
    {
        $client = static::createClient();
        $client->followRedirects();
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
    public function test_visibility_add_nested_album(string $album1Visibility, string $album3Visibility, string $album2FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user, 'visibility' => $album1Visibility]);

        // Act
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album3Visibility]);

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $album2FinalVisibility]);
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
    public function test_visibility_change_parent_album(string $newAlbumVisibility, string $album2Visibility, string $album1FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['parent' => null, 'owner' => $user]);

        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album2Visibility]);
        $photo2 = PhotoFactory::createOne(['album' => $albumLevel2, 'owner' => $user]);

        // Act
        $newParentAlbum = AlbumFactory::createOne(['owner' => $user, 'visibility' => $newAlbumVisibility]);
        $albumLevel2->_withoutAutoRefresh(
            function (Album $albumLevel2) use ($newParentAlbum) {
                $albumLevel2->setParent($newParentAlbum->_real());
            }
        );
        $albumLevel2->_save();

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $album1FinalVisibility]);
        PhotoFactory::assert()->exists(['id' => $photo2->getId(), 'finalVisibility' => $album1FinalVisibility]);
    }

    #[TestWith(['public', 'public', 'public', 'public'])]
    #[TestWith(['public', 'private', 'public', 'private'])]
    #[TestWith(['public', 'internal', 'public', 'internal'])]

    #[TestWith(['internal', 'public', 'internal', 'internal'])]
    #[TestWith(['internal', 'private', 'internal', 'private'])]
    #[TestWith(['internal', 'internal', 'internal', 'internal'])]

    #[TestWith(['private', 'public', 'private', 'private'])]
    #[TestWith(['private', 'private', 'private', 'private'])]
    #[TestWith(['private', 'internal', 'private', 'private'])]
    public function test_visibility_change_album_visibility(string $album1Visibility, string $album2Visibility, string $level1Visibility, string $level2Visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['parent' => null, 'owner' => $user]);
        $photo1 = PhotoFactory::createOne(['album' => $albumLevel1, 'owner' => $user]);

        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user, 'visibility' => $album2Visibility]);
        $photo2 = PhotoFactory::createOne(['album' => $albumLevel2, 'owner' => $user]);

        // Act
        $albumLevel1->_withoutAutoRefresh(
            function (Album $albumLevel1) use ($album1Visibility) {
                $albumLevel1->setVisibility($album1Visibility);
            }
        );

        $albumLevel1->_save();

        // Assert
        AlbumFactory::assert()->exists(['id' => $albumLevel1->getId(), 'finalVisibility' => $level1Visibility]);
        PhotoFactory::assert()->exists(['id' => $photo1->getId(), 'finalVisibility' => $level1Visibility]);
        AlbumFactory::assert()->exists(['id' => $albumLevel2->getId(), 'finalVisibility' => $level2Visibility]);
        PhotoFactory::assert()->exists(['id' => $photo2->getId(), 'finalVisibility' => $level2Visibility]);
    }
}
