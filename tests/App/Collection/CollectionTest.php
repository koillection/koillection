<?php

declare(strict_types=1);

namespace App\Tests\App\Collection;

use App\Enum\DateFormatEnum;
use App\Enum\DatumTypeEnum;
use App\Enum\DisplayModeEnum;
use App\Enum\SortingDirectionEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CollectionTest extends AppTestCase
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

    public function test_can_get_collection_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        CollectionFactory::createMany(3, ['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-element'));
    }

    public function test_can_edit_collection_index(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        CollectionFactory::createMany(3, ['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/collections/edit');
        $crawler = $this->client->submitForm('Submit', [
            'display_configuration[displayMode]' => DisplayModeEnum::DISPLAY_MODE_LIST,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.list-element'));
    }

    public function test_can_get_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne(['currency' => 'EUR', 'dateFormat' => DateFormatEnum::FORMAT_SLASH_DMY])->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user, 'visibility' => VisibilityEnum::VISIBILITY_PRIVATE]);
        CollectionFactory::createMany(3, ['parent' => $collection, 'owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $collection, 'owner' => $user]);
        $choiceList = ChoiceListFactory::createOne(['name' => 'Progress', 'choices' => ['In progress', 'Done', 'Abandoned'], 'owner' => $user]);
        $file = $this->createFile('txt');
        $filename = $file->getFilename();

        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Japanese title', 'value' => '葬送のフリーレン']);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 2, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volumes', 'value' => '12']);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 3, 'type' => DatumTypeEnum::TYPE_COUNTRY, 'label' => 'Country', 'value' => 'JP']);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 4, 'type' => DatumTypeEnum::TYPE_DATE, 'label' => 'Release date', 'value' => '2022-03-03']);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 5, 'type' => DatumTypeEnum::TYPE_CHOICE_LIST, 'label' => 'Progress', 'value' => json_encode(['In progress']), 'choiceList' => $choiceList]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 6, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'New', 'value' => true]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 7, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'Lent', 'value' => false]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 8, 'type' => DatumTypeEnum::TYPE_FILE, 'label' => 'File', 'fileFile' => $file]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($collection->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('.collection-header .visibility .fa-lock'));
        $this->assertCount(3, $crawler->filter('.collection-element'));
        $this->assertCount(3, $crawler->filter('.collection-item'));

        $this->assertCount(8, $crawler->filter('.datum-row'));
        $this->assertSame('Japanese title : 葬送のフリーレン', $crawler->filter('.datum-row')->eq(0)->text());
        $this->assertSame('Volumes : 12', $crawler->filter('.datum-row')->eq(1)->text());
        $this->assertSame('Country : 🇯🇵 (Japan)', $crawler->filter('.datum-row')->eq(2)->text());
        $this->assertSame('Release date : 03/03/2022', $crawler->filter('.datum-row')->eq(3)->text());
        $this->assertSame('Progress : In progress', $crawler->filter('.datum-row')->eq(4)->text());
        $this->assertSame('New :', $crawler->filter('.datum-row')->eq(5)->text());
        $this->assertCount(1, $crawler->filter('.datum-row')->eq(5)->filter('.fa-check.font-green'));
        $this->assertSame('Lent :', $crawler->filter('.datum-row')->eq(6)->text());
        $this->assertCount(1, $crawler->filter('.datum-row')->eq(6)->filter('.fa-close.font-red'));

        $this->assertSame("File : {$filename} (104 B)", $crawler->filter('.datum-row')->eq(7)->text());
        $this->assertFileExists($crawler->filter('.datum-row')->eq(7)->filter('a')->attr('href'));
    }

    public function test_can_get_collection_with_list_view(): void
    {
        // Arrange
        $user = UserFactory::createOne(['currency' => 'EUR', 'dateFormat' => DateFormatEnum::FORMAT_SLASH_DMY])->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);

        $collection->getItemsDisplayConfiguration()
            ->setDisplayMode(DisplayModeEnum::DISPLAY_MODE_LIST)
            ->setColumns(['Author'])
            ->setSortingProperty('Author')
            ->setSortingType(DatumTypeEnum::TYPE_TEXT)
            ->setSortingDirection(SortingDirectionEnum::DESCENDING)
        ;
        $collection->_save();
        $collection->getChildrenDisplayConfiguration()
            ->setDisplayMode(DisplayModeEnum::DISPLAY_MODE_LIST)
            ->setColumns(['Author'])
            ->setSortingProperty('Author')
            ->setSortingType(DatumTypeEnum::TYPE_TEXT)
            ->setSortingDirection(SortingDirectionEnum::DESCENDING)
        ;
        $collection->_save();

        $child1 = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $child1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        $child2 = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $child2, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        $child3 = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $child3, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);

        $item1 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        $item2 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item2, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        $item3 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item3, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertEquals($collection->getTitle(), $crawler->filter('h1')->text());

        $this->assertCount(3, $crawler->filter('.children-table tbody tr'));
        $this->assertCount(7, $crawler->filter('.children-table thead th'));
        $this->assertSame('', $crawler->filter('.children-table thead th')->eq(0)->text());
        $this->assertSame('Name', $crawler->filter('.children-table thead th')->eq(1)->text());
        $this->assertSame('Author', $crawler->filter('.children-table thead th')->eq(2)->text());
        $this->assertSame('Collections', $crawler->filter('.children-table thead th')->eq(3)->text());
        $this->assertSame('Items', $crawler->filter('.children-table thead th')->eq(4)->text());
        $this->assertSame('Visibility', $crawler->filter('.children-table thead th')->eq(5)->text());
        $this->assertSame('Actions', $crawler->filter('.children-table thead th')->eq(6)->text());

        $this->assertCount(3, $crawler->filter('.items-table tbody tr'));
        $this->assertCount(5, $crawler->filter('.items-table thead th'));
        $this->assertSame('', $crawler->filter('.items-table thead th')->eq(0)->text());
        $this->assertSame('Name', $crawler->filter('.items-table thead th')->eq(1)->text());
        $this->assertSame('Author', $crawler->filter('.items-table thead th')->eq(2)->text());
        $this->assertSame('Visibility', $crawler->filter('.items-table thead th')->eq(3)->text());
        $this->assertSame('Actions', $crawler->filter('.items-table thead th')->eq(4)->text());
    }

    public function test_can_post_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $parent = CollectionFactory::createOne(['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/collections/add?parent=' . $parent->getId());
        $crawler = $this->client->submitForm('Submit', [
            'collection[file]' => 'data:image/png;base64,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',
            'collection[title]' => 'Frieren',
            'collection[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC
        ]);

        // Assert
        $this->assertSame('Frieren', $crawler->filter('h1')->text());
    }

    public function test_can_edit_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        $collection = CollectionFactory::createOne(['owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);

        CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        $item1 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Publisher']);
        $item2 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item2, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Author']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Publisher']);

        $fileDatum = DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 1, 'type' => DatumTypeEnum::TYPE_FILE, 'label' => 'File', 'fileFile' => $this->createFile('txt')]);
        $oldFileDatumPath = $fileDatum->getFile();

        // Act
        $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'collection[title]' => 'Berserk',
            'collection[visibility]' => VisibilityEnum::VISIBILITY_PUBLIC,
            'collection[itemsDisplayConfiguration][label]' => 'One-shots',
            'collection[childrenDisplayConfiguration][label]' => 'Series',
            'collection[data][0][position]' => 1, 'collection[data][0][type]' => DatumTypeEnum::TYPE_FILE, 'collection[data][0][label]' => 'File', 'collection[data][0][fileFile]' => $this->createFile('txt'),
        ]);

        // Assert
        $this->assertSame('Berserk', $crawler->filter('h1')->text());
        $this->assertSame('Series', $crawler->filter('h2')->eq(1)->text());
        $this->assertSame('One-shots', $crawler->filter('h2')->eq(2)->text());
        $this->assertSame($collection->getImage(), $crawler->filter('img')->eq(1)->attr('src'));
        $this->assertFileExists($collection->getImage());

        $this->assertFileDoesNotExist($oldFileDatumPath);
        $this->assertFileExists($fileDatum->getFile());
    }

    public function test_can_delete_collection_image(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['title' => 'Berserk', 'owner' => $user, 'image' => $this->createFile('png')->getRealPath()]);
        $oldCollectionImagePath = $collection->getImage();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'collection[deleteImage]' => true,
        ]);

        // Assert
        $this->assertSame('B', $crawler->filter('.collection-header')->filter('.thumbnail')->text());
        $this->assertFileDoesNotExist($oldCollectionImagePath);
    }

    public function test_can_get_collection_items_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $childCollection = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $collection, 'owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $childCollection, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId() . '/items');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame($collection->getTitle(), $crawler->filter('h1')->text());
        $this->assertCount(6, $crawler->filter('.collection-item'));
    }

    public function test_can_delete_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $collection, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/collections/' . $collection->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_collection_index');
        CollectionFactory::assert()->count(0);
        ItemFactory::assert()->count(0);
    }

    public function test_can_delete_child_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $childCollection = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        $otherCollection = CollectionFactory::createOne(['owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $collection, 'owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $childCollection, 'owner' => $user]);
        ItemFactory::createMany(3, ['collection' => $otherCollection, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/collections/' . $childCollection->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_collection_show', ['id' => $collection->getId()]);
        CollectionFactory::assert()->count(2);
        ItemFactory::assert()->count(6);
    }

    public function test_can_batch_tag_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $childCollection = CollectionFactory::createOne(['parent' => $collection, 'owner' => $user]);
        $item = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        $childItem = ItemFactory::createOne(['collection' => $childCollection, 'owner' => $user]);
        $tag = TagFactory::createOne(['label' => 'Frieren', 'owner' => $user])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/collections/' . $collection->getId() . '/batch-tagging');
        $this->client->submitForm('Submit', [
            'batch_tagger[tags]' => json_encode(['Frieren']),
            'batch_tagger[recursive]' => true
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame($item->getTags()->first()->getId(), $tag->getId());
        $this->assertSame($childItem->getTags()->first()->getId(), $tag->getId());
    }
}
