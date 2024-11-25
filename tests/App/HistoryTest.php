<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Tests\AppTestCase;
use App\Tests\Factory\AlbumFactory;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\InventoryFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\LogFactory;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Factory\TagCategoryFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TemplateFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\WishFactory;
use App\Tests\Factory\WishlistFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class HistoryTest extends AppTestCase
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

    public function test_can_see_history(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user]);
        ItemFactory::createOne(['collection' => $collection, 'name' => 'Frieren #1', 'owner' => $user]);

        $tagCategory = TagCategoryFactory::createOne(['label' => 'Trip', 'owner' => $user]);
        TagFactory::createOne(['category' => $tagCategory, 'label' => 'Trip to Japan', 'owner' => $user]);

        $album = AlbumFactory::createOne(['title' => 'Home', 'owner' => $user]);
        PhotoFactory::createOne(['album' => $album, 'title' => 'Main shelf', 'owner' => $user]);

        $wishlist = WishlistFactory::createOne(['name' => 'Books', 'owner' => $user]);
        WishFactory::createOne(['wishlist' => $wishlist, 'name' => 'Frieren #2', 'owner' => $user]);

        TemplateFactory::createOne(['name' => 'Books', 'owner' => $user]);
        ChoiceListFactory::createOne(['name' => 'Status', 'owner' => $user]);
        InventoryFactory::createOne(['name' => 'Collection', 'owner' => $user]);

        $collection->_delete();
        // Refresh logs because they are updated in LoggableListener with a native query, Foundry isn't aware of those changes
        foreach (LogFactory::all() as $log) {
            $log->_refresh();
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/history?search_history[classes][]=collection&search_history[classes][]=item&search_history[classes][]=tag&search_history[classes][]=tagCategory&search_history[classes][]=album&search_history[classes][]=photo&search_history[classes][]=wishlist&search_history[classes][]=wish&search_history[classes][]=template&search_history[classes][]=choiceList&search_history[classes][]=inventory&search_history[types][]=create&search_history[types][]=delete');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('History', $crawler->filter('h1')->text());
        $this->assertCount(13, $crawler->filter('tbody tr'));

        $logs = $crawler->filter('tbody')->text();
        $this->assertStringContainsString('Collection Frieren was created', $logs);
        $this->assertStringContainsString('Item Frieren #1 was created', $logs);

        $this->assertStringContainsString('Tag category Trip was created', $logs);
        $this->assertStringContainsString('Tag Trip to Japan was created', $logs);

        $this->assertStringContainsString('Album Home was created', $logs);
        $this->assertStringContainsString('Photo Main shelf was created', $logs);

        $this->assertStringContainsString('Wishlist Books was created', $logs);
        $this->assertStringContainsString('Wish Frieren #2 was created', $logs);

        $this->assertStringContainsString('Template Books was created', $logs);
        $this->assertStringContainsString('Choice list Status was created', $logs);
        $this->assertStringContainsString('Inventory Collection was created', $logs);

        $this->assertStringContainsString('Item Frieren #1 was deleted', $logs);
        $this->assertStringContainsString('Collection Frieren was deleted', $logs);
    }

    public function test_ajax_history(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $user]);
        ItemFactory::createOne(['collection' => $collection, 'name' => 'Frieren #1', 'owner' => $user]);

        // Act
        $crawler = $this->client->xmlHttpRequest('GET', '/history?search_history[term]=Frieren&search_history[classes][]=collection&search_history[classes][]=item&search_history[classes][]=tag&search_history[classes][]=tagCategory&search_history[classes][]=album&search_history[classes][]=photo&search_history[classes][]=wishlist&search_history[classes][]=wish&search_history[classes][]=template&search_history[classes][]=choiceList&search_history[classes][]=inventory&search_history[types][]=create&search_history[types][]=delete');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertCount(2, $crawler->filter('tbody tr'));
    }
}
