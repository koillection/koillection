<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Service\PasswordUpdater;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, entity: User::class)]
#[AsEntityListener(event: Events::preRemove, entity: User::class)]
final readonly class UserListener
{
    public function __construct(
        private PasswordUpdater $passwordUpdater
    ) {
    }

    public function prePersist(User $user): void
    {
        $this->passwordUpdater->hashPassword($user);
    }

    public function preUpdate(User $user): void
    {
        $this->passwordUpdater->hashPassword($user);
    }

    public function preRemove(User $user, PreRemoveEventArgs $args): void
    {
        $user->setWishlistsDisplayConfiguration(null);
        $user->setCollectionsDisplayConfiguration(null);
        $user->setAlbumsDisplayConfiguration(null);
        $args->getObjectManager()->flush();
    }
}
