<?php

declare(strict_types=1);

namespace App\Tests\App\Album;

use App\Entity\Album;
use App\Service\CachedValuesGetter;
use App\Service\RefreshCachedValuesQueue;
use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class AlbumCountersTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    public ?RefreshCachedValuesQueue $refreshCachedValuesQueue;

    public ?CachedValuesGetter $cachedValuesGetter;

    #[\Override]
    protected function setUp(): void
    {
        $this->refreshCachedValuesQueue = $this->getContainer()->get(RefreshCachedValuesQueue::class);
        $this->cachedValuesGetter = $this->getContainer()->get(CachedValuesGetter::class);
    }

    /*
     * When adding a new child, all parent counters must be increased by 1
     */
    public function test_add_child_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);

        // Act
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(2, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['children']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['children']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['children']);
    }

    /*
     * When moving a child:
     * - Decrease all old parents albums counters by the number of children album belonging to the child + 1 (itself)
     * - Decrease all old parents photos counters by the number of photos in the child and in all the child's children
     * - Increase all new parents albums counters by the number of children album belonging to the child + 1 (itself)
     * - Increase all new parents photos counters by the number of photos in the child and in all the child's children
     */
    public function test_move_child_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['parent' => null, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel1, 'owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel2, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel3, 'owner' => $user]);
        $albumLevel4 = AlbumFactory::createOne(['parent' => $albumLevel3, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel4, 'owner' => $user]);

        // Act
        $user = UserFactory::createOne()->_real();
        $newParentAlbum = AlbumFactory::createOne(['owner' => $user]);
        $albumLevel3->_withoutAutoRefresh(
            function (Album $albumLevel3) use ($newParentAlbum) {
                $albumLevel3->setParent($newParentAlbum->_real());
            }
        );
        $albumLevel3->_save();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($newParentAlbum->_real())['counters']['photos']);
        $this->assertSame(2, $this->cachedValuesGetter->getCachedValues($newParentAlbum->_real())['counters']['children']);

        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['photos']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['children']);

        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['photos']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($albumLevel4->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel4->_real())['counters']['children']);
    }

    /*
   * When deleting a child:
   * - Decrease all old parents albums counters by the number of children album belonging to the child + 1 (itself)
   * - Decrease all old parents photos counters by the number of photos in the child and in all the child's children
   */
    public function test_delete_child_album(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel1, 'owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel2, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel3, 'owner' => $user]);
        $albumLevel4 = AlbumFactory::createOne(['parent' => $albumLevel3, 'owner' => $user]);
        PhotoFactory::createMany(3, ['album' => $albumLevel4, 'owner' => $user]);

        // Act
        $albumLevel3->_delete();
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['photos']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['children']);
    }

    /*
     * When adding a new photo, all parent counters must be increased by 1
     */
    public function test_add_photo(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);
        PhotoFactory::createOne(['album' => $albumLevel3, 'owner' => $user]);

        // Act
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['photos']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['photos']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['photos']);
    }

    /*
     * When moving a photo, all parent new counters must be increased by 1 and old parent counters decreased by 1
     */
    public function test_move_photo(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);
        $photo = PhotoFactory::createOne(['album' => $albumLevel3, 'owner' => $user]);

        // Act
        $newAlbum = AlbumFactory::createOne(['owner' => $user]);
        $photo->setAlbum($newAlbum->_real());
        $photo->_save();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($newAlbum->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['photos']);
    }

    /*
     * When deleting an photo decrease all old parents albums counters by one
     */
    public function test_delete_photo(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $albumLevel1 = AlbumFactory::createOne(['owner' => $user]);
        $albumLevel2 = AlbumFactory::createOne(['parent' => $albumLevel1, 'owner' => $user]);
        $albumLevel3 = AlbumFactory::createOne(['parent' => $albumLevel2, 'owner' => $user]);
        $photo = PhotoFactory::createOne(['album' => $albumLevel3, 'owner' => $user]);

        // Act
        $photo->_delete();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel1->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel2->_real())['counters']['photos']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($albumLevel3->_real())['counters']['photos']);
    }
}
