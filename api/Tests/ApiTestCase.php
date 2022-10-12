<?php

declare(strict_types=1);

namespace Api\Tests;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ApiTestCase extends \ApiPlatform\Symfony\Bundle\Test\ApiTestCase
{
    protected ?TranslatorInterface $translator = null;

    protected ?EntityManagerInterface $em = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->translator = $this->getContainer()->get('translator');
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->iriConverter = $this->getContainer()->get('api_platform.iri_converter');
    }

    protected function createClientWithCredentials(User $user): Client
    {
        $encoder = $this->getContainer()->get(JWTEncoderInterface::class);
        $payload = [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles()
        ];

        return static::createClient([], ['headers' => ['Authorization' => 'Bearer ' . $encoder->encode($payload)]]);
    }
}
