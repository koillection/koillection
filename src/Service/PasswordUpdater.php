<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordUpdater
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function hashPassword(User $user): User
    {
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword) {
            return $user;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->eraseCredentials();

        return $user;
    }
}
