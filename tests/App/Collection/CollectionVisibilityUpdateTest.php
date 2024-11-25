<?php

declare(strict_types=1);

namespace App\Tests\App\Collection;

use App\Entity\Collection;
use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CollectionVisibilityUpdateTest extends AppTestCase
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
    public function test_visibility_add_nested_collection(string $collection1Visibility, string $collection3Visibility, string $collection2FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionLevel1 = CollectionFactory::createOne(['owner' => $user, 'visibility' => $collection1Visibility]);

        // Act
        $collectionLevel2 = CollectionFactory::createOne(['parent' => $collectionLevel1, 'owner' => $user, 'visibility' => $collection3Visibility]);

        // Assert
        CollectionFactory::assert()->exists(['id' => $collectionLevel2->getId(), 'finalVisibility' => $collection2FinalVisibility]);
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
    public function test_visibility_change_parent_collection(string $newCollectionVisibility, string $collection2Visibility, string $collection1FinalVisibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionLevel1 = CollectionFactory::createOne(['parent' => null, 'owner' => $user]);

        $collectionLevel2 = CollectionFactory::createOne(['parent' => $collectionLevel1, 'owner' => $user, 'visibility' => $collection2Visibility]);
        $item2 = ItemFactory::createOne(['collection' => $collectionLevel2, 'owner' => $user]);
        $datumCollection2 = DatumFactory::createOne(['collection' => $collectionLevel2, 'owner' => $user]);
        $datumItem2 = DatumFactory::createOne(['item' => $item2, 'owner' => $user]);

        // Act
        $newParentCollection = CollectionFactory::createOne(['owner' => $user, 'visibility' => $newCollectionVisibility]);
        $collectionLevel2->_withoutAutoRefresh(
            function (Collection $collectionLevel2) use ($newParentCollection) {
                $collectionLevel2->setParent($newParentCollection->_real());
            }
        );
        $collectionLevel2->_save();

        // Assert
        CollectionFactory::assert()->exists(['id' => $collectionLevel2->getId(), 'finalVisibility' => $collection1FinalVisibility]);
        DatumFactory::assert()->exists(['id' => $datumCollection2->getId(), 'finalVisibility' => $collection1FinalVisibility]);
        ItemFactory::assert()->exists(['id' => $item2->getId(), 'finalVisibility' => $collection1FinalVisibility]);
        DatumFactory::assert()->exists(['id' => $datumItem2->getId(), 'finalVisibility' => $collection1FinalVisibility]);
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
    public function test_visibility_change_collection_visibility(string $collection1Visibility, string $collection2Visibility, string $level1Visibility, string $level2Visibility): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $collectionLevel1 = CollectionFactory::createOne(['parent' => null, 'owner' => $user]);
        $item1 = ItemFactory::createOne(['collection' => $collectionLevel1, 'owner' => $user]);
        $datumCollection1 = DatumFactory::createOne(['collection' => $collectionLevel1, 'owner' => $user]);
        $datumItem1 = DatumFactory::createOne(['item' => $item1, 'owner' => $user]);

        $collectionLevel2 = CollectionFactory::createOne(['parent' => $collectionLevel1, 'owner' => $user, 'visibility' => $collection2Visibility]);
        $item2 = ItemFactory::createOne(['collection' => $collectionLevel2, 'owner' => $user]);
        $datumCollection2 = DatumFactory::createOne(['collection' => $collectionLevel2, 'owner' => $user]);
        $datumItem2 = DatumFactory::createOne(['item' => $item2, 'owner' => $user]);

        // Act
        $collectionLevel1->_withoutAutoRefresh(
            function (Collection $collectionLevel1) use ($collection1Visibility) {
                $collectionLevel1->setVisibility($collection1Visibility);
            }
        );
        $collectionLevel1->_save();

        // Assert
        CollectionFactory::assert()->exists(['id' => $collectionLevel1->getId(), 'finalVisibility' => $level1Visibility]);
        ItemFactory::assert()->exists(['id' => $item1->getId(), 'finalVisibility' => $level1Visibility]);
        DatumFactory::assert()->exists(['id' => $datumCollection1->getId(), 'finalVisibility' => $level1Visibility]);
        DatumFactory::assert()->exists(['id' => $datumItem1->getId(), 'finalVisibility' => $level1Visibility]);

        CollectionFactory::assert()->exists(['id' => $collectionLevel2->getId(), 'finalVisibility' => $level2Visibility]);
        ItemFactory::assert()->exists(['id' => $item2->getId(), 'finalVisibility' => $level2Visibility]);
        DatumFactory::assert()->exists(['id' => $datumCollection2->getId(), 'finalVisibility' => $level2Visibility]);
        DatumFactory::assert()->exists(['id' => $datumItem2->getId(), 'finalVisibility' => $level2Visibility]);
    }
}
