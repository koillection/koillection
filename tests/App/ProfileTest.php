<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Tests\AppTestCase;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProfileTest extends AppTestCase
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

    public function test_can_edit_profile(): void
    {
        // Arrange
        $user = UserFactory::createOne(['avatar' => $this->createFile('png')->getRealPath()])->_real();
        $this->client->loginUser($user);
        $avatarPath = $user->getAvatar();

        // Act
        $this->client->request(Request::METHOD_GET, '/profile');
        $this->client->submitForm('Submit', [
            'profile[username]' => 'Stitch',
            'profile[email]' => 'stitch@koillection.com'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        UserFactory::assert()->exists(['username' => 'Stitch', 'email' => 'stitch@koillection.com']);
        $this->assertFileExists($avatarPath);
    }

    public function test_can_delete_avatar_image(): void
    {
        // Arrange
        $user = UserFactory::createOne(['username' => 'Stitch', 'avatar' => $this->createFile('png')->getRealPath()])->_real();
        $this->client->loginUser($user);
        $oldAvatarPath = $user->getAvatar();

        // Act
        $this->client->request(Request::METHOD_GET, '/profile');
        $crawler = $this->client->submitForm('Submit', [
            'profile[deleteAvatar]' => true,
        ]);

        // Assert
        $this->assertSame('S', $crawler->filter('.user-avatar')->text());
        $this->assertFileDoesNotExist($oldAvatarPath);
    }
}
