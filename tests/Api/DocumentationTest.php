<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class DocumentationTest extends WebTestCase
{
    private KernelBrowser $client;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function test_can_see_api_documentation(): void
    {
        // Arrange

        // Act
        $this->client->request(Request::METHOD_GET, '/api/index.html');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleSame('Koillection API - API Platform');
    }
}
