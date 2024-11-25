<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'kernel.request')]
final readonly class ActivityListener
{
    public function __construct(
        private Security $security,
        private ManagerRegistry $managerRegistry
    ) {
    }

    public function onKernelRequest()
    {
        $user = $this->security->getUser();
        $now = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);

        if (!$user instanceof User || $user->getLastDateOfActivity() == $now) {
            return null;
        }

        $user->setLastDateOfActivity($now);
        $this->managerRegistry->getManager()->flush();
        return null;
    }
}
