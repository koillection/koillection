<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\InventoryFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class InventoryTest extends AppTestCase
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

    public function test_can_add_inventory(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['title' => 'Friren #1', 'owner' => $user]);
        $artbookCollection = CollectionFactory::createOne(['title' => 'Artbook', 'parent' => $collection, 'owner' => $user]);
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_GET, '/inventories/add');
        $crawler = $this->client->submitForm('Submit', [
            'inventory[name]' => 'Collection',
            'inventory[content]' => implode(',', [$collection->getId(), $artbookCollection->getId()])
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collection', $crawler->filter('h1')->text());
        $this->assertSame('1 item', $crawler->filter('.nav-pills li')->eq(0)->text());
        $this->assertSame('0 checked items', $crawler->filter('.nav-pills li')->eq(1)->text());
        $this->assertSame('0% completed', $crawler->filter('.nav-pills li')->eq(2)->text());
    }

    public function test_can_delete_inventory(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $inventory = InventoryFactory::createOne(['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/inventories/' . $inventory->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/inventories/' . $inventory->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_tools_index');
        InventoryFactory::assert()->count(0);
    }

    public function test_can_get_inventory(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $inventory = InventoryFactory::createOne(['name' => 'Collection', 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/inventories/' . $inventory->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collection', $crawler->filter('h1')->text());
    }

    public function test_can_check(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $collection = CollectionFactory::createOne(['title' => 'Friren #1', 'owner' => $user]);
        $item = ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $user]);
        $inventory = InventoryFactory::createOne(['name' => 'Collection', 'owner' => $user]);

        // Act
        $this->client->request(Request::METHOD_POST, '/inventories/' . $inventory->getId() . '/check?id=' . $item->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
