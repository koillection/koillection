<?php

declare(strict_types=1);

namespace App\Tests\App\Wishlist;

use App\Entity\Wishlist;
use App\Service\CachedValuesGetter;
use App\Service\RefreshCachedValuesQueue;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishFactory;
use App\Tests\Factory\WishlistFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishlistCountersTest extends AppTestCase
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
    public function test_add_child_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);

        // Act
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(2, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['children']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['children']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['children']);
    }

    /*
     * When moving a child:
     * - Decrease all old parents wishlists counters by the number of children wishlist belonging to the child + 1 (itself)
     * - Decrease all old parents wishes counters by the number of wishes in the child and in all the child's children
     * - Increase all new parents wishlists counters by the number of children wishlist belonging to the child + 1 (itself)
     * - Increase all new parents wishes counters by the number of wishes in the child and in all the child's children
     */
    public function test_move_child_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['parent' => null, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel2, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel3, 'owner' => $user]);
        $wishlistLevel4 = WishlistFactory::createOne(['parent' => $wishlistLevel3, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel4, 'owner' => $user]);

        // Act
        $newParentWishlist = WishlistFactory::createOne(['owner' => $user]);
        $wishlistLevel3->_withoutAutoRefresh(
            function (Wishlist $wishlistLevel3) use ($newParentWishlist) {
                $wishlistLevel3->setParent($newParentWishlist->_real());
            }
        );
        $wishlistLevel3->_save();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($newParentWishlist->_real())['counters']['wishes']);
        $this->assertSame(2, $this->cachedValuesGetter->getCachedValues($newParentWishlist->_real())['counters']['children']);

        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['wishes']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['children']);

        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['wishes']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($wishlistLevel4->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel4->_real())['counters']['children']);
    }

    /*
   * When deleting a child:
   * - Decrease all old parents wishlists counters by the number of children wishlist belonging to the child + 1 (itself)
   * - Decrease all old parents wishes counters by the number of wishes in the child and in all the child's children
   */
    public function test_delete_child_wishlist(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel2, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel3, 'owner' => $user]);
        $wishlistLevel4 = WishlistFactory::createOne(['parent' => $wishlistLevel3, 'owner' => $user]);
        WishFactory::createMany(3, ['wishlist' => $wishlistLevel4, 'owner' => $user]);

        // Act
        $wishlistLevel3->_delete();
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(6, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['wishes']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['children']);

        $this->assertSame(3, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['children']);
    }

    /*
     * When adding a new wish, all parent counters must be increased by 1
     */
    public function test_add_wish(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);
        WishFactory::createOne(['wishlist' => $wishlistLevel3, 'owner' => $user]);

        // Act
        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['wishes']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['wishes']);
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['wishes']);
    }

    /*
     * When moving a wish, all parent new counters must be increased by 1 and old parent counters decreased by 1
     */
    public function test_move_wish(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);
        $wish = WishFactory::createOne(['wishlist' => $wishlistLevel3, 'owner' => $user]);

        // Act
        $newWishlist = WishlistFactory::createOne(['owner' => $user]);
        $wish->setWishlist($newWishlist->_real());
        $wish->_save();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(1, $this->cachedValuesGetter->getCachedValues($newWishlist->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['wishes']);
    }

    /*
     * When deleting a wish decrease all old parents wishlists counters by one
     */
    public function test_delete_wish(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user]);
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user]);
        $wishlistLevel3 = WishlistFactory::createOne(['parent' => $wishlistLevel2, 'owner' => $user]);
        $wish = WishFactory::createOne(['wishlist' => $wishlistLevel3, 'owner' => $user]);

        // Act
        $wish->_delete();

        $this->refreshCachedValuesQueue->process();

        // Assert
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel1->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel2->_real())['counters']['wishes']);
        $this->assertSame(0, $this->cachedValuesGetter->getCachedValues($wishlistLevel3->_real())['counters']['wishes']);
    }
}
