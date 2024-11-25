<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Tests\AppTestCase;
use App\Tests\Factory\TagCategoryFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TagCategoryTest extends AppTestCase
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

    public function test_can_see_tag_category_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        TagCategoryFactory::createMany(3, ['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/tag-categories');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Tag categories', $crawler->filter('h1')->text());
        $this->assertCount(3, $crawler->filter('.list-element'));
    }

    public function test_can_see_tag_category(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $tagCategory = TagCategoryFactory::createOne(['owner' => $user])->_real();
        TagFactory::createMany(3, ['category' => $tagCategory, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/tag-categories/' . $tagCategory->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame($tagCategory->getLabel(), $crawler->filter('h1')->text());
        $this->assertSame('3 tags', $crawler->filter('.title-block .nav-pills')->eq(0)->text());

        $this->assertSame('Tags', $crawler->filter('h2')->eq(0)->text());
        $this->assertCount(3, $crawler->filter('.tag'));
    }

    public function test_can_add_tag_category(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/tag-categories/add');

        $crawler = $this->client->submitForm('Submit', [
            'tag_category[label]' => 'Person',
            'tag_category[color]' => '009688',
            'tag_category[description]' => 'Description'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Person', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(0)->text());
    }

    public function test_can_edit_tag_category(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $tagCategory = TagCategoryFactory::createOne(['owner' => $user])->_real();

        // Act
        $this->client->request(Request::METHOD_GET, '/tag-categories/' . $tagCategory->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'tag_category[label]' => 'Company',
            'tag_category[color]' => '009688',
            'tag_category[description]' => 'Description'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Company', $crawler->filter('h1')->text());
    }

    public function test_can_delete_tag_category(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $tagCategory = TagCategoryFactory::createOne(['owner' => $user]);
        TagFactory::createOne(['category' => $tagCategory, 'owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/tag-categories/' . $tagCategory->getId());
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/tag-categories/' . $tagCategory->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        TagCategoryFactory::assert()->count(0);
        TagFactory::assert()->count(1);
    }
}
