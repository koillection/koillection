<?php

declare(strict_types=1);

namespace App\Tests\App\Item;

use App\Entity\Item;
use App\Entity\Wishlist;
use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ItemVisibilityUpdateTest extends AppTestCase
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
    public function test_visibility_add_item(string $collection1Visibility, string $collection2Visibility, string $itemFinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionLevel1 = CollectionFactory::createOne(['owner' => $user, 'visibility' => $collection1Visibility]);
        $collectionLevel2 = CollectionFactory::createOne(['parent' => $collectionLevel1, 'owner' => $user, 'visibility' => $collection2Visibility]);

        // Act
        $item = ItemFactory::createOne(['owner' => $user, 'collection' => $collectionLevel2]);

        // Assert
        ItemFactory::assert()->exists(['id' => $item->getId(), 'finalVisibility' => $itemFinalVisibility]);
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
    public function test_visibility_change_item_collection(string $collection1Visibility, string $collection2Visibility, string $itemFinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $oldCollection = CollectionFactory::createOne(['owner' => $user]);
        $item = ItemFactory::createOne(['collection' => $oldCollection, 'owner' => $user]);
        $datum = DatumFactory::createOne(['item' => $item, 'owner' => $user]);

        $collectionLevel1 = CollectionFactory::createOne(['owner' => $user, 'visibility' => $collection1Visibility]);
        $collectionLevel2 = CollectionFactory::createOne(['parent' => $collectionLevel1, 'owner' => $user, 'visibility' => $collection2Visibility]);

        // Act
        $item->_withoutAutoRefresh(
            function (Item $item) use ($collectionLevel2) {
                $item->setCollection($collectionLevel2->_real());
            }
        );
        $item->_save();

        // Assert
        ItemFactory::assert()->exists(['id' => $item->getId(), 'finalVisibility' => $itemFinalVisibility]);
        DatumFactory::assert()->exists(['id' => $datum->getId(), 'finalVisibility' => $itemFinalVisibility]);
    }
}
