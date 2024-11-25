<?php

declare(strict_types=1);

namespace App\Tests\App\Admin;

use App\Enum\RoleEnum;
use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends AppTestCase
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

    public function test_admin_can_access_users_list(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);

        // Act
        $this->client->request(Request::METHOD_GET, '/admin/users');

        // Assert
        $this->assertResponseIsSuccessful();
    }

    public function test_admin_can_post_a_user(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);

        // Act
        $this->client->request(Request::METHOD_GET, '/admin/users/add');
        $this->client->submitForm('submit', [
            'user[username]' => 'Stitch',
            'user[email]' => 'stitch@koillection.com',
            'user[plainPassword][first]' => 'password1234',
            'user[plainPassword][second]' => 'password1234',
            'user[diskSpaceAllowed]' => 536870912,
            'user[timezone]' => 'Pacific/Honolulu',
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        UserFactory::assert()->exists(['username' => 'Stitch', 'email' => 'stitch@koillection.com']);
    }

    public function test_admin_can_edit_a_user(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);

        // Act
        $this->client->request(Request::METHOD_GET, '/admin/users/' . $admin->getId() . '/edit');
        $this->client->submitForm('submit', [
            'user[username]' => 'admin',
            'user[email]' => 'admin-new-email@test.com',
            'user[plainPassword][first]' => 'password1234',
            'user[plainPassword][second]' => 'password1234',
            'user[diskSpaceAllowed]' => 536870912,
            'user[timezone]' => 'Europe/Paris'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        UserFactory::assert()->exists(['username' => 'admin', 'email' => 'admin-new-email@test.com']);
    }

    public function test_admin_can_delete_a_user(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);
        $user = UserFactory::createOne(['roles' => [RoleEnum::ROLE_USER]])->_real();
        $userId = $user->getId();

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/users');
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/admin/users/' . $user->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        UserFactory::assert()->notExists(['id' => $userId]);
    }

    public function test_cant_delete_admin(): void
    {
        // Arrange
        $admin = UserFactory::createOne(['roles' => [RoleEnum::ROLE_ADMIN]])->_real();
        $this->client->loginUser($admin);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/admin/users');
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/admin/users/' . $admin->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        UserFactory::assert()->exists(['id' => $admin->getId()]);
    }
}
