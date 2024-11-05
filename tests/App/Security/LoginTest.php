<?php

declare(strict_types=1);

namespace App\Tests\App\Security;

use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class LoginTest extends AppTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects();
    }

    public function test_can_login(): void
    {
        // Arrange
        $user = UserFactory::createOne(['plainPassword' => '123456789aaaAAA']);

        // Act
        $this->client->request('GET', '/');
        $crawler = $this->client->submitForm('Sign in', [
            '_login' => $user->getUsername(),
            '_password' => '123456789aaaAAA'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Collections', $crawler->filter('h1')->text());
    }

    public function test_user_cant_login_with_bad_credentials(): void
    {
        // Arrange
        $user = UserFactory::createOne(['plainPassword' => 'password']);

        // Act
        $this->client->request('GET', '/');
        $crawler = $this->client->submitForm('Sign in', [
            '_login' => $user->getUsername(),
            '_password' => 'wrong password'
        ]);

        // Assert
        $this->assertSame('Welcome to Koillection', $crawler->filter('h1')->text());
        $this->assertSame('Invalid credentials.', $crawler->filter('.error-helper')->text());
    }

    public function test_not_enabled_user_cant_login(): void
    {
        // Arrange
        $user = UserFactory::createOne(['enabled' => false, 'plainPassword' => '123456789aaaAAA']);

        // Act
        $this->client->request('GET', '/');
        $crawler = $this->client->submitForm('Sign in', [
            '_login' => $user->getUsername(),
            '_password' => '123456789aaaAAA'
        ]);

        // Assert
        $this->assertSame('Welcome to Koillection', $crawler->filter('h1')->text());
        $this->assertSame('User not activated', $crawler->filter('.error-helper')->text());
    }
}
