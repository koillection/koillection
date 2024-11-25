<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Wishlist;
use App\Model\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    #[\Override]
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('w')
            ->orderBy('w.name', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllExcludingItselfAndChildren(Wishlist $wishlist): array
    {
        if (!$wishlist->getCreatedAt() instanceof \DateTimeImmutable) {
            return $this->findAll();
        }

        $excludedWishlists = $wishlist->getChildrenRecursively();
        $excludedWishlists[] = $wishlist->getId();

        return $this
            ->createQueryBuilder('w')
            ->orderBy('w.name', Criteria::ASC)
            ->where('w NOT IN (:excludedWishlists)')
            ->setParameter('excludedWishlists', $excludedWishlists)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForSearch(Search $search): array
    {
        $qb = $this
            ->createQueryBuilder('w')
            ->orderBy('w.name', Criteria::ASC)
        ;

        if (\is_string($search->getTerm()) && $search->getTerm() !== '') {
            $qb
                ->andWhere('LOWER(w.name) LIKE LOWER(:term)')
                ->setParameter('term', '%' . $search->getTerm() . '%')
            ;
        }

        if ($search->getCreatedAt() instanceof \DateTimeImmutable) {
            $createdAt = $search->getCreatedAt();
            $qb
                ->andWhere('w.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $createdAt->setTime(0, 0, 0))
                ->setParameter('end', $createdAt->setTime(23, 59, 59))
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
