<?php

declare(strict_types=1);

namespace App\Tests\App;

use App\Tests\AppTestCase;
use App\Tests\Factory\ChoiceListFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ChoiceListTest extends AppTestCase
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

    public function test_can_see_choice_list_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/choice-lists');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Choice lists', $crawler->filter('h1')->text());
    }

    public function test_can_post_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);

        // Act
        $this->client->request(Request::METHOD_GET, '/choice-lists/add');

        $crawler = $this->client->submitForm('Submit', [
            'choice_list[name]' => 'Progress'
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Progress', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(0)->text());
    }

    public function test_can_edit_choice_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $choiceList = ChoiceListFactory::createOne(['owner' => $user, 'choices' => ['New', 'Test', 'Done']]);

        // Act
        $this->client->request(Request::METHOD_GET, '/choice-lists/' . $choiceList->getId() . '/edit');
        $crawler = $this->client->submitForm('Submit', [
            'choice_list[name]' => 'Progress',
            'choice_list[choices][0]' => 'New',
            'choice_list[choices][1]' => 'In progress',
            'choice_list[choices][2]' => 'Done',
        ]);

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertSame('Progress', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(0)->text());
        $this->assertSame('New, In progress, Done', $crawler->filter('.list-element')->eq(0)->filter('td')->eq(1)->text());
    }

    public function test_can_delete_choice_list(): void
    {
        // Arrange
        $user = UserFactory::createOne()->_real();
        $this->client->loginUser($user);
        $choiceList = ChoiceListFactory::createOne(['owner' => $user]);

        // Act
        $crawler = $this->client->request(Request::METHOD_GET, '/choice-lists/');
        $crawler->filter('#modal-delete form')->getNode(0)->setAttribute('action', '/choice-lists/' . $choiceList->getId() . '/delete');
        $this->client->submitForm('OK');

        // Assert
        $this->assertResponseIsSuccessful();
        ChoiceListFactory::assert()->count(0);
    }
}
