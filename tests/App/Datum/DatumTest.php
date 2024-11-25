<?php

declare(strict_types=1);

namespace App\Tests\App\Datum;

use App\Enum\DatumTypeEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DatumTest extends AppTestCase
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

    public function test_can_get_html_by_type(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        foreach (DatumTypeEnum::TYPES as $type) {
            if ($type !== DatumTypeEnum::TYPE_CHOICE_LIST) {
                // Act
                $this->client->request(Request::METHOD_GET, '/datum/' . $type);

                // Assert
                $this->assertResponseIsSuccessful();
                $this->assertResponseHeaderSame('Content-Type', 'application/json');
            }
        }
    }

    public function test_can_get_html_for_type_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $choiceList = ChoiceListFactory::createOne(['owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/datum/choice-list/' . $choiceList->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    /**
     * Authors will be kept with its value because all items have it AND share the same value
     * Volumes will be kept but not its value because all items have it BUT they have different values
     * Comment won't be kept, because not all items have it.
     */
    public function test_can_get_common_fields(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $item1 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'position' => 2, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volume', 'value' => '1']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item1, 'position' => 3, 'type' => DatumTypeEnum::TYPE_TEXTAREA, 'label' => 'Comment', 'value' => 'Not opened']);
        $item2 = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item2, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);
        DatumFactory::createOne(['owner' => $user, 'item' => $item2, 'position' => 2, 'type' => DatumTypeEnum::TYPE_NUMBER, 'label' => 'Volume', 'value' => '2']);

        // Act
        $this->client->request(Request::METHOD_GET, '/datum/load-common-fields/' . $collection->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $content);

        $this->assertSame('Authors', $content[0][1]);
        $this->assertSame('Abe Tsukasa, Yamada Kanehito', (new Crawler($content[0][2]))->filter('#data___placeholder___value')->attr('value'));

        $this->assertSame('Volume', $content[1][1]);
        $this->assertSame('', (new Crawler($content[1][2]))->filter('#data___placeholder___value')->attr('value'));
    }

    public function test_can_get_collection_fields(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 1, 'type' => DatumTypeEnum::TYPE_TEXT, 'label' => 'Authors', 'value' => 'Abe Tsukasa, Yamada Kanehito']);
        DatumFactory::createOne(['owner' => $user, 'collection' => $collection, 'position' => 2, 'type' => DatumTypeEnum::TYPE_COUNTRY, 'label' => 'Country', 'value' => 'JP']);

        // Act
        $this->client->request(Request::METHOD_GET, '/datum/load-collection-fields/' . $collection->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $content);

        $this->assertSame('Authors', $content[0][1]);
        $this->assertSame('Abe Tsukasa, Yamada Kanehito', (new Crawler($content[0][2]))->filter('#data___placeholder___value')->attr('value'));

        $this->assertSame('Country', $content[1][1]);
        $this->assertSame('JP', (new Crawler($content[1][2]))->filter('#data___placeholder___value option[selected]')->attr('value'));
    }

    public function test_unavailable_choice_from_choice_list_still_usable_on_item(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['owner' => $user]);
        $item = ItemFactory::createOne(['collection' => $collection, 'owner' => $user]);
        $choiceList = ChoiceListFactory::createOne(['name' => 'Progress', 'choices' => ['In progress', 'Done', 'Abandoned'], 'owner' => $user]);
        DatumFactory::createOne(['owner' => $user, 'item' => $item, 'position' => 5, 'type' => DatumTypeEnum::TYPE_CHOICE_LIST, 'label' => 'Status', 'value' => json_encode(['In progress', 'Fake']), 'choiceList' => $choiceList]);

        // Act
        $this->client->request(Request::METHOD_GET, '/items/' . $item->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'item[name]' => $item->getName(),
            'item[collection]' => $collection->getId(),
            'item[data][0][position]' => 1, 'item[data][0][type]' => DatumTypeEnum::TYPE_CHOICE_LIST, 'item[data][0][label]' => 'Status', 'item[data][0][value]' => 'Fake'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Status : Fake', $crawler->filter('.datum-row')->eq(0)->text());
    }
}
