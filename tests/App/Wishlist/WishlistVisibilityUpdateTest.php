<?php

declare(strict_types=1);

namespace App\Tests\App\Wishlist;

use App\Entity\Wishlist;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishFactory;
use App\Tests\Factory\WishlistFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class WishlistVisibilityUpdateTest extends AppTestCase
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
    public function test_visibility_add_nested_wishlist(string $wishlist1Visibility, string $wishlist3Visibility, string $wishlist2FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['owner' => $user, 'visibility' => $wishlist1Visibility]);

        // Act
        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user, 'visibility' => $wishlist3Visibility]);

        // Assert
        WishlistFactory::assert()->exists(['id' => $wishlistLevel2->getId(), 'finalVisibility' => $wishlist2FinalVisibility]);
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
    public function test_visibility_change_parent_wishlist(string $newWishlistVisibility, string $wishlist2Visibility, string $wishlist1FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['parent' => null, 'owner' => $user]);

        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user, 'visibility' => $wishlist2Visibility]);
        $wish2 = WishFactory::createOne(['wishlist' => $wishlistLevel2, 'owner' => $user]);

        // Act
        $newParentWishlist = WishlistFactory::createOne(['owner' => $user, 'visibility' => $newWishlistVisibility]);
        $wishlistLevel2->_withoutAutoRefresh(
            function (Wishlist $wishlistLevel2) use ($newParentWishlist) {
                $wishlistLevel2->setParent($newParentWishlist->_real());
            }
        );
        $wishlistLevel2->_save();

        // Assert
        WishlistFactory::assert()->exists(['id' => $wishlistLevel2->getId(), 'finalVisibility' => $wishlist1FinalVisibility]);
        WishFactory::assert()->exists(['id' => $wish2->getId(), 'finalVisibility' => $wishlist1FinalVisibility]);
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
    public function test_visibility_change_wishlist_visibility(string $wishlist1Visibility, string $wishlist2Visibility, string $level1Visibility, string $level2Visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $wishlistLevel1 = WishlistFactory::createOne(['parent' => null, 'owner' => $user]);
        $wish1 = WishFactory::createOne(['wishlist' => $wishlistLevel1, 'owner' => $user]);

        $wishlistLevel2 = WishlistFactory::createOne(['parent' => $wishlistLevel1, 'owner' => $user, 'visibility' => $wishlist2Visibility]);
        $wish2 = WishFactory::createOne(['wishlist' => $wishlistLevel2, 'owner' => $user]);

        // Act
        $wishlistLevel1->_withoutAutoRefresh(
            function (Wishlist $wishlistLevel1) use ($wishlist1Visibility) {
                $wishlistLevel1->setVisibility($wishlist1Visibility);
            }
        );
        $wishlistLevel1->_save();

        // Assert
        WishlistFactory::assert()->exists(['id' => $wishlistLevel1->getId(), 'finalVisibility' => $level1Visibility]);
        WishFactory::assert()->exists(['id' => $wish1->getId(), 'finalVisibility' => $level1Visibility]);
        WishlistFactory::assert()->exists(['id' => $wishlistLevel2->getId(), 'finalVisibility' => $level2Visibility]);
        WishFactory::assert()->exists(['id' => $wish2->getId(), 'finalVisibility' => $level2Visibility]);
    }
}
