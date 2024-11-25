<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Enum\DatumTypeEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\DatumFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SignatureTest extends AppTestCase
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

    public function test_can_see_signature_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $items = ItemFactory::createMany(3, [
            'owner' => $user,
            'collection' => CollectionFactory::createOne(['owner' => $user]),
        ]);
        foreach ($items as $item) {
            $item->_real()->addData(DatumFactory::createOne(['owner' => $user, 'type' => DatumTypeEnum::TYPE_SIGN])->_real());
        }

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/signatures');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Signatures', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.collection-item'));
    }
}
