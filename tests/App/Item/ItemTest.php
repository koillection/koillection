<?php

declare(strict_types=1);

namespace App\Tests\App\Item;

use App\Enum\DateFormatEnum;
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\FieldFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\LoanFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TemplateFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ItemTest extends AppTestCase
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

    public function test_can_get_item(): void
    {
        // Arrange
        $user = UserFactory::createOne(['currency' => 'EUR', 'dateFormat' => DateFormatEnum::FORMAT_SLASH_DMY])->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();
        $relatedItem = ItemFactory::createOne(['name' => 'Calendar Frieren 2023', 'collection' => $collection, 'owner' => $user])->_real();
        $choiceList = ChoiceListFactory::createOne(['name' => 'Edition', 'choices' => ['Normal', 'Collector'], 'owner' => $user]);
        ItemFactory::createOne(['name' => 'Frieren #4', 'collection' => $collection, 'owner' => $user]);
        ItemFactory::createOne(['name' => 'Frieren #6', 'collection' => $collection, 'owner' => $user]);

        $item = ItemFactory::createOne([
            'name' => 'Frieren #5',
            'image' => $this->createFile('png'),
            'collection' => $collection,
            'owner' => $user,
            'visibility' => VisibilityEnum::VISIBILITY_PRIVATE,
            'quantity' => 2,
        ]);
        $tag = TagFactory::createOne(['owner' => $user, 'label' => 'Abe Tsukasa'])->_real();
        $item->addTag($tag);
        $item->addTag(TagFactory::createOne(['owner' => $user, 'label' => 'Yamada Kanehito'])->_real());
        $item->addRelatedItem($relatedItem);
        $item->_save();

        $file = $this->createFile('txt');
        $filename = $file->getFilename();

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 2, 'type' => DatumTypeEnum::TYPE_TEXTAREA, 'label' => 'Description', 'value' => 'Frieren est un shōnen manga écrit par Yamada Kanehito et dessiné par Abe Tsukasa.']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 3, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volume', 'value' => '1']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 4, 'type' => DatumTypeEnum::TYPE_PRICE, 'currency' => 'EUR', 'label' => 'Price', 'value' => '7.95']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 5, 'type' => DatumTypeEnum::TYPE_COUNTRY, 'label' => 'Country', 'value' => 'JP']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 6, 'type' => DatumTypeEnum::TYPE_DATE, 'label' => 'Release date', 'value' => '2022-03-03']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 7, 'type' => DatumTypeEnum::TYPE_RATING, 'label' => 'Rating', 'value' => '10']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 8, 'type' => DatumTypeEnum::TYPE_LINK, 'label' => 'Wiki page', 'value' => 'https://ja.wikipedia.org/wiki/%E8%91%AC%E9%80%81%E3%81%AE%E3%83%95%E3%83%AA%E3%83%BC%E3%83%AC%E3%83%B3']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 9, 'type' => DatumTypeEnum::TYPE_CHOICE_LIST, 'label' => 'Edition', 'value' => json_encode(['Collector']), 'choiceList' => $choiceList]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 10, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'New', 'value' => true]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 11, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'Lent', 'value' => false]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 12, 'type' => DatumTypeEnum::TYPE_FILE, 'label' => 'File', 'fileFile' => $file]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 13, 'type' => DatumTypeEnum::TYPE_LIST, 'label' => 'List', 'value' => json_encode(['Test1', 'Test2'])]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_SIGN, 'label' => 'Sign', 'fileImage' => $this->createFile('png')]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 2, 'type' => DatumTypeEnum::TYPE_IMAGE, 'label' => 'Image', 'fileImage' => $this->createFile('png')]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/items/' . $item->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Frieren #5', $crawler->filter('h1')->innerText());
        $this->assertSame('(x2)', $crawler->filter('h1 .quantity')->innerText());
        $this->assertCount(1, $crawler->filter('.collection-header .visibility .fa-lock'));

        $this->assertCount(3, $crawler->filter('.slider-frame a'));
        $this->assertFileExists($crawler->filter('.slider-frame a')->eq(0)->filter('img')->attr('src'));
        $this->assertFileExists($crawler->filter('.slider-frame a')->eq(1)->filter('img')->attr('src'));
        $this->assertSame('Sign', $crawler->filter('.slider-frame a')->eq(1)->filter('.image-label')->text());
        $this->assertFileExists($crawler->filter('.slider-frame a')->eq(2)->filter('img')->attr('src'));
        $this->assertSame('Image', $crawler->filter('.slider-frame a')->eq(2)->filter('.image-label')->text());

        $this->assertCount(2, $crawler->filter('.tag'));
        $this->assertSame('Abe Tsukasa', $crawler->filter('.tag')->eq(0)->text());
        $this->assertSame('Yamada Kanehito', $crawler->filter('.tag')->eq(1)->text());

        $this->assertCount(13, $crawler->filter('.datum-row'));
        $this->assertSame('Authors : Abe Tsukasa, Yamada Kanehito', $crawler->filter('.datum-row')->eq(0)->text());
        $this->assertCount(2, $crawler->filter('.datum-row')->eq(0)->filter('a'));
        $this->assertSame('Abe Tsukasa', $crawler->filter('.datum-row')->eq(0)->filter('a')->eq(0)->text());
        $this->assertSame('/tags/' . $tag->getId(), $crawler->filter('.datum-row')->eq(0)->filter('a')->eq(0)->attr('href'));
        $this->assertSame('Description : Frieren est un shōnen manga écrit par Yamada Kanehito et dessiné par Abe Tsukasa.', $crawler->filter('.datum-row')->eq(1)->text());
        $this->assertSame('Volume : 1', $crawler->filter('.datum-row')->eq(2)->text());
        $this->assertSame('Price : €7.95', $crawler->filter('.datum-row')->eq(3)->text());
        $this->assertSame('Country : 🇯🇵 (Japan)', $crawler->filter('.datum-row')->eq(4)->text());
        $this->assertSame('Release date : 03/03/2022', $crawler->filter('.datum-row')->eq(5)->text());
        $this->assertSame('Rating :', $crawler->filter('.datum-row .label')->eq(6)->text());
        $this->assertCount(10, $crawler->filter('.datum-row')->eq(6)->filter('.star-rating-view.colored'));
        $this->assertSame('Wiki page :', $crawler->filter('.datum-row .label')->eq(7)->text());
        $this->assertSame(substr('https://ja.wikipedia.org/wiki/%E8%91%AC%E9%80%81%E3%81%AE%E3%83%95%E3%83%AA%E3%83%BC%E3%83%AC%E3%83%B3', 0, 47) . '...', $crawler->filter('.datum-row')->eq(7)->filter('a')->text());
        $this->assertSame('Edition : Collector', $crawler->filter('.datum-row')->eq(8)->text());

        $this->assertSame('New :', $crawler->filter('.datum-row')->eq(9)->text());
        $this->assertCount(1, $crawler->filter('.datum-row')->eq(9)->filter('.fa-check.font-green'));
        $this->assertSame('Lent :', $crawler->filter('.datum-row')->eq(10)->text());
        $this->assertCount(1, $crawler->filter('.datum-row')->eq(10)->filter('.fa-close.font-red'));

        $this->assertSame("File : {$filename} (104 B)", $crawler->filter('.datum-row')->eq(11)->text());
        $this->assertFileExists($crawler->filter('.datum-row')->eq(11)->filter('a')->attr('href'));

        $this->assertSame('List : Test1 Test2', $crawler->filter('.datum-row')->eq(12)->text());

        $this->assertCount(1, $crawler->filter('.related-items img'));
        $this->assertSame('Calendar Frieren 2023', $crawler->filter('.related-items img')->eq(0)->attr('alt'));

        $this->assertCount(1, $crawler->filter('[data-swipe-target="previous"]'));
        $this->assertSame('Frieren #4', $crawler->filter('[data-swipe-target="previous"]')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('[data-swipe-target="next"]'));
        $this->assertSame('Frieren #6', $crawler->filter('[data-swipe-target="next"]')->eq(0)->text());
    }

    public function test_can_get_item_with_null_data(): void
    {
        // Arrange
        $user = UserFactory::createOne(['currency' => 'EUR', 'dateFormat' => DateFormatEnum::FORMAT_SLASH_DMY])->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();

        $item = ItemFactory::createOne([
            'name' => 'Frieren #5',
            'image' => $this->createFile('png'),
            'collection' => $collection,
            'owner' => $user,
            'visibility' => VisibilityEnum::VISIBILITY_PRIVATE,
            'quantity' => 1,
        ]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 2, 'type' => DatumTypeEnum::TYPE_TEXTAREA, 'label' => 'Description', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 3, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volume', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 4, 'type' => DatumTypeEnum::TYPE_PRICE, 'currency' => 'EUR', 'label' => 'Price', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 5, 'type' => DatumTypeEnum::TYPE_COUNTRY, 'label' => 'Country', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 6, 'type' => DatumTypeEnum::TYPE_DATE, 'label' => 'Release date', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 7, 'type' => DatumTypeEnum::TYPE_RATING, 'label' => 'Rating', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 8, 'type' => DatumTypeEnum::TYPE_LINK, 'label' => 'Wiki page', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 9, 'type' => DatumTypeEnum::TYPE_CHOICE_LIST, 'label' => 'Edition', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 10, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'New', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 11, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'Lent', 'value' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 12, 'type' => DatumTypeEnum::TYPE_FILE, 'label' => 'File', 'fileFile' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 13, 'type' => DatumTypeEnum::TYPE_LIST, 'label' => 'List', 'value' => null]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_SIGN, 'label' => 'Sign', 'fileImage' => null]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 2, 'type' => DatumTypeEnum::TYPE_IMAGE, 'label' => 'Image', 'fileImage' => null]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/items/' . $item->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Frieren #5', $crawler->filter('h1')->innerText());
    }

    public function test_can_create_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();
        $uploadedFile = $this->createFile('png');

        // Act
        $this->client->request(Request::METHOD_GET, '/items/add?collection=' . $collection->getId());
        $this->client->submitForm('Submit', [
            'item[name]' => 'Frieren #1',
            'item[file]' => $uploadedFile,
            'item[collection]' => $collection->getId(),
            'item[quantity]' => 1,
            'item[visibility]' => VisibilityEnum::VISIBILITY_PRIVATE,
            'item[tags]' => json_encode(['Manga', 'Frieren'])
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        ItemFactory::assert()->exists([
            'name' => 'Frieren #1',
            'collection' => $collection->getId(),
            'visibility' => VisibilityEnum::VISIBILITY_PRIVATE,
            'owner' => $user->getId()
        ]);

        $this->assertFileExists(ItemFactory::first()->getImage());
        TagFactory::assert()->exists(['label' => 'Manga', 'owner' => $user->getId()]);
        TagFactory::assert()->exists(['label' => 'Frieren', 'owner' => $user->getId()]);
    }

    public function test_can_create_item_then_create_another(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/items/add?collection=' . $collection->getId());
        $crawler = $this->client->submitForm('Submit and add another item', [
            'item[name]' => 'Frieren #1',
            'item[collection]' => $collection->getId(),
            'item[quantity]' => 1,
            'item[visibility]' => VisibilityEnum::VISIBILITY_PRIVATE,
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Add a new item', $crawler->filter('h1')->innerText());
        ItemFactory::assert()->exists([
            'name' => 'Frieren #1',
            'collection' => $collection->getId(),
            'visibility' => VisibilityEnum::VISIBILITY_PRIVATE,
            'owner' => $user->getId()
        ]);
    }

    public function test_cant_create_item_without_collection(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/items/add');

        // Assert
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    public function test_can_load_item_form_with_default_template(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $template = TemplateFactory::createOne(['owner' => $user]);
        FieldFactory::createMany(3, ['template' => $template, 'owner' => $user]);
        $collection = CollectionFactory::createOne(['itemsDefaultTemplate' => $template, 'owner' => $user])->_real();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/items/add?collection=' . $collection->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $crawler->filter('.datum'));
    }

    public function test_can_edit_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user, 'file' => $this->createFile('png')]);
        $oldImagePath = $item->getImage();
        $choiceList = ChoiceListFactory::createOne(['name' => 'Edition', 'choices' => ['Normal', 'Collector'], 'owner' => $user]);

        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 2, 'type' => DatumTypeEnum::TYPE_TEXTAREA, 'label' => 'Description', 'value' => 'Frieren est un shōnen manga écrit par Yamada Kanehito et dessiné par Abe Tsukasa.']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 3, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volume', 'value' => '1']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 4, 'type' => DatumTypeEnum::TYPE_PRICE, 'currency' => 'EUR', 'label' => 'Price', 'value' => '7.95']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 5, 'type' => DatumTypeEnum::TYPE_COUNTRY, 'label' => 'Country', 'value' => 'JP']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 6, 'type' => DatumTypeEnum::TYPE_DATE, 'label' => 'Release date', 'value' => '2022-03-03']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 7, 'type' => DatumTypeEnum::TYPE_RATING, 'label' => 'Rating', 'value' => '10']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 8, 'type' => DatumTypeEnum::TYPE_LINK, 'label' => 'Wiki page', 'value' => 'https://ja.wikipedia.org/wiki/%E8%91%AC%E9%80%81%E3%81%AE%E3%83%95%E3%83%AA%E3%83%BC%E3%83%AC%E3%83%B3']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 9, 'type' => DatumTypeEnum::TYPE_CHOICE_LIST, 'label' => 'Edition', 'value' => json_encode(['Collector']), 'choiceList' => $choiceList]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 10, 'type' => DatumTypeEnum::TYPE_CHECKBOX, 'label' => 'New', 'value' => false]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 11, 'type' => DatumTypeEnum::TYPE_LIST, 'label' => 'List', 'value' => json_encode(['Test1', 'Test2'])]);
        $fileDatum = DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 12, 'type' => DatumTypeEnum::TYPE_FILE, 'label' => 'File', 'fileFile' => $this->createFile('txt')]);
        $oldFileDatumPath = $fileDatum->getFile();
        $imageDatum = DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 13, 'type' => DatumTypeEnum::TYPE_IMAGE, 'label' => 'Image', 'fileImage' => $this->createFile('png')]);
        $oldImageDatumPath = $imageDatum->getImage();
        $signDatum = DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 14, 'type' => DatumTypeEnum::TYPE_SIGN, 'label' => 'Sign', 'fileImage' => $this->createFile('png')]);
        $oldSignDatumPath = $signDatum->getImage();
        $videoDatum = DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 15, 'type' => DatumTypeEnum::TYPE_VIDEO, 'label' => 'Video', 'fileVideo' => $this->createFile('mp4')]);
        $oldVideoDatumPath = $videoDatum->getVideo();

        // Act
        $this->client->request(Request::METHOD_GET, '/items/' . $item->getId() . '/edit');
        $this->client->submitForm('Submit', [
            'item[name]' => 'Berserk #1',
            'item[collection]' => $collection->getId(),
            'item[file]' => $this->createFile('jpeg'),
            'item[data][0][position]' => 1, 'item[data][0][type]' => DatumTypeEnum::TYPE_TEXT, 'item[data][0][label]' => 'Authors', 'item[data][0][value]' => 'Abe Tsukasa, Yamada Kanehito',
            'item[data][1][position]' => 2, 'item[data][1][type]' => DatumTypeEnum::TYPE_TEXTAREA, 'item[data][1][label]' => 'Description', 'item[data][1][value]' => 'Frieren est un shōnen manga écrit par Yamada Kanehito et dessiné par Abe Tsukasa.',
            'item[data][2][position]' => 3, 'item[data][2][type]' => DatumTypeEnum::TYPE_NUMBER, 'item[data][2][label]' => 'Volume', 'item[data][2][value]' => '1',
            'item[data][3][position]' => 4, 'item[data][3][type]' => DatumTypeEnum::TYPE_PRICE, 'item[data][3][label]' => 'Price', 'item[data][3][value]' => '7.95',
            'item[data][4][position]' => 5, 'item[data][4][type]' => DatumTypeEnum::TYPE_COUNTRY, 'item[data][4][label]' => 'Country', 'item[data][4][value]' => 'JP',
            'item[data][5][position]' => 6, 'item[data][5][type]' => DatumTypeEnum::TYPE_DATE, 'item[data][5][label]' => 'Release date', 'item[data][5][value]' => '2022-03-03',
            'item[data][6][position]' => 7, 'item[data][6][type]' => DatumTypeEnum::TYPE_RATING, 'item[data][6][label]' => 'Rating', 'item[data][6][value]' => '10',
            'item[data][7][position]' => 8, 'item[data][7][type]' => DatumTypeEnum::TYPE_LINK, 'item[data][7][label]' => 'Wiki page', 'item[data][7][value]' => 'https://ja.wikipedia.org/wiki/%E8%91%AC%E9%80%81%E3%81%AE%E3%83%95%E3%83%AA%E3%83%BC%E3%83%AC%E3%83%B3',
            'item[data][8][position]' => 9, 'item[data][8][type]' => DatumTypeEnum::TYPE_CHOICE_LIST, 'item[data][8][label]' => 'Edition', 'item[data][8][value]' => 'Collector',
            'item[data][9][position]' => 10, 'item[data][9][type]' => DatumTypeEnum::TYPE_CHECKBOX, 'item[data][9][label]' => 'New', 'item[data][9][value]' => true,
            'item[data][10][position]' => 11, 'item[data][10][type]' => DatumTypeEnum::TYPE_LIST, 'item[data][10][label]' => 'List', 'item[data][10][value]' => json_encode(['Test1', 'Test2']),
            'item[data][11][position]' => 12, 'item[data][11][type]' => DatumTypeEnum::TYPE_FILE, 'item[data][11][label]' => 'File', 'item[data][11][fileFile]' => $this->createFile('txt'),
            'item[data][12][position]' => 13, 'item[data][12][type]' => DatumTypeEnum::TYPE_IMAGE, 'item[data][12][label]' => 'Image', 'item[data][12][fileImage]' => $this->createFile('avif'),
            'item[data][13][position]' => 14, 'item[data][13][type]' => DatumTypeEnum::TYPE_SIGN, 'item[data][13][label]' => 'Sign', 'item[data][13][fileImage]' => $this->createFile('webp'),
            'item[data][14][position]' => 15, 'item[data][14][type]' => DatumTypeEnum::TYPE_VIDEO, 'item[data][14][label]' => 'Video', 'item[data][14][fileVideo]' => $this->createFile('mp4'),
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        ItemFactory::assert()->exists([
            'name' => 'Berserk #1',
            'collection' => $collection->getId(),
            'owner' => $user->getId(),
        ]);

        $this->assertFileDoesNotExist($oldImagePath);
        $this->assertFileDoesNotExist($oldFileDatumPath);
        $this->assertFileDoesNotExist($oldImageDatumPath);
        $this->assertFileDoesNotExist($oldSignDatumPath);
        $this->assertFileDoesNotExist($oldVideoDatumPath);

        $this->assertFileExists($item->getImage());
        $this->assertFileExists($fileDatum->getFile());
        $this->assertFileExists($imageDatum->getImage());
        $this->assertFileExists($signDatum->getImage());
        $this->assertFileExists($videoDatum->getVideo());
    }

    public function test_can_delete_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $item = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/items/' . $item->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/items/' . $item->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        CollectionFactory::assert()->count(1);
        ItemFactory::assert()->notExists(0);
        DatumFactory::assert()->count(0);
    }

    public function test_can_loan_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $item = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/items/' . $item->getId() . '/loan');
        $this->client->submitForm('Submit', [
            'loan[lentAt]' => '2022-10-28',
            'loan[lentTo]' => 'Someone'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        LoanFactory::assert()->exists([
            'item' => $item->getId()
        ]);
    }

    public function test_can_autocomplete_related_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user]);
        ItemFactory::createOne(['name' => 'Berserk #1', 'collection' => $collection, 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/items/autocomplete/fri');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertSame('Frieren #1', $content[0]['text']);
    }

    public function test_cant_have_multiple_data_with_same_label(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user])->_real();
        $item = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['label' => 'Author', 'item' => $item, 'owner' => $user]);
        DatumFactory::createOne(['label' => 'Author', 'item' => $item, 'owner' => $user]);

        // Act
        $errors = $this->getContainer()->get(ValidatorInterface::class)->validate($item->_real());

        // Assert
        $this->assertCount(1, $errors);
        $this->assertSame('"Author" label is used multiple times, all labels must be unique', $errors[0]->getMessage());
    }
}
