<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Template;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class TemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }

    #[\Override]
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('t')
            ->orderBy('t.name', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllWithCounters(): array
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT t as template, COUNT(DISTINCT f) as fieldsCounter')
            ->from(Template::class, 't')
            ->leftJoin('t.fields', 'f')
            ->groupBy('t.id')
            ->orderBy('t.name', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithItems(string $id): ?Template
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.fields', 'f')
            ->addSelect('f')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findById(string $id): ?Template
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.fields', 'f')
            ->addSelect('f')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
