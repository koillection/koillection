<?php

declare(strict_types=1);

namespace App\Tests\App\Admin;

use App\Enum\RoleEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\CollectionFactory;
use App\Tests\Factory\ItemFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class DashboardTest extends AppTestCase
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

    public function test_admin_can_access_dashboard(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);

        // Act
        $this->client->request(Request::METHOD_GET, '/admin');

        // Assert
        $this->assertResponseIsSuccessful();
    }

    public function test_can_refresh_caches(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);
        $collection = CollectionFactory::createOne(['title' => 'Frieren', 'owner' => $admin])->_real();
        ItemFactory::createOne(['name' => 'Frieren #1', 'collection' => $collection, 'owner' => $admin])->_real();
        ItemFactory::createOne(['name' => 'Frieren #2', 'collection' => $collection, 'owner' => $admin])->_real();
        ItemFactory::createOne(['name' => 'Frieren #3', 'collection' => $collection, 'owner' => $admin])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/admin/refresh-caches');

        // Assert
        $this->assertResponseIsSuccessful();
    }
}
