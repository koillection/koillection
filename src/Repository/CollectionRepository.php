<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\Datum;
use App\Enum\DatumTypeEnum;
use App\Enum\DisplayModeEnum;
use App\Model\Search\Search;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    #[\Override]
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->orderBy('c.title', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllExcludingItselfAndChildren(Collection $collection): array
    {
        if (!$collection->getCreatedAt() instanceof \DateTimeImmutable) {
            return $this->findAll();
        }

        $excludedCollections = $collection->getChildrenRecursively();
        $excludedCollections[] = $collection->getId();

        return $this
            ->createQueryBuilder('c')
            ->orderBy('c.title', Criteria::ASC)
            ->where('c NOT IN (:excludedCollections)')
            ->setParameter('excludedCollections', $excludedCollections)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllWithItems(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.items', 'i')
            ->addSelect('i')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->orderBy('c.title', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllWithChildren(): array
    {
        return $this
            ->createQueryBuilder('c')
            ->leftJoin('c.children', 'ch')
            ->addSelect('ch')
            ->orderBy('c.title', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findWithItemsAndData(string $id): ?Collection
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('c.items', 'i')
            ->leftJoin('i.data', 'd')
            ->addSelect('i, d')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findForSearch(Search $search): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->orderBy('c.title', Criteria::ASC)
        ;

        if (\is_string($search->getTerm()) && $search->getTerm() !== '') {
            $whereClause = 'LOWER(c.title) LIKE LOWER(:term)';

            if ($search->getSearchInData()) {
                $whereClause = 'LOWER(c.title) LIKE LOWER(:term) OR LOWER(d.value) LIKE LOWER(:term)';
                $qb
                    ->leftJoin('c.data', 'd', 'WITH', 'd.type IN (:types)')
                    ->setParameter('types', DatumTypeEnum::AVAILABLE_FOR_SEARCH)
                ;
            }

            $qb
                ->andWhere($whereClause)
                ->setParameter('term', '%' . $search->getTerm() . '%')
            ;
        }

        if ($search->getCreatedAt() instanceof \DateTimeImmutable) {
            $createdAt = $search->getCreatedAt();
            $qb
                ->andWhere('c.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $createdAt->setTime(0, 0, 0))
                ->setParameter('end', $createdAt->setTime(23, 59, 59))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function suggestItemsLabels(Collection $collection): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('config.label')
            ->distinct()
            ->leftJoin('c.itemsDisplayConfiguration', 'config')
            ->where('c.parent = :parent')
            ->andWhere('config.label IS NOT NULL')
            ->setParameter('parent', $collection->getParent())
        ;

        if (null !== $collection->getItemsDisplayConfiguration()->getLabel()) {
            $qb
                ->andWhere('config.label != :label')
                ->setParameter('label', $collection->getItemsDisplayConfiguration()->getLabel())
            ;
        }

        return array_column($qb->getQuery()->getArrayResult(), 'label');
    }

    public function suggestChildrenLabels(Collection $collection): array
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('config.label')
            ->distinct()
            ->leftJoin('c.childrenDisplayConfiguration', 'config')
            ->where('c.parent = :parent')
            ->andWhere('config.label IS NOT NULL')
            ->setParameter('parent', $collection->getParent())
        ;

        if (null !== $collection->getChildrenDisplayConfiguration()->getLabel()) {
            $qb
                ->andWhere('config.label != :label')
                ->setParameter('label', $collection->getChildrenDisplayConfiguration()->getLabel())
            ;
        }

        return array_column($qb->getQuery()->getArrayResult(), 'label');
    }

    public function findForOrdering(Collection $collection, bool $asArray = false): array
    {
        if ($collection->getChildrenDisplayConfiguration()->getSortingProperty()) {
            // Get ordering value
            $subQuery = $this->getEntityManager()
                ->createQueryBuilder()
                ->select('datum.value')
                ->from(Datum::class, 'datum')
                ->where('datum.collection = child')
                ->andWhere('datum.label = :label')
                ->andWhere('datum.type IN (:types)')
                ->setMaxResults(1)
            ;

            $qb = $this
                ->createQueryBuilder('child')
                ->addSelect("({$subQuery}) AS orderingValue, data")
                ->where('child.parent = :parent')
                ->setParameter('parent', $collection->getId())
                ->setParameter('label', $collection->getChildrenDisplayConfiguration()->getSortingProperty())
                ->setParameter('types', DatumTypeEnum::TEXT_TYPES)
            ;

            // If list, preload datum used for ordering and in columns
            if (DisplayModeEnum::DISPLAY_MODE_LIST === $collection->getChildrenDisplayConfiguration()->getDisplayMode()) {
                $qb
                    ->leftJoin('child.data', 'data', 'WITH', 'data.label = :label OR data.label IN (:labels) OR data IS NULL')
                    ->setParameter('labels', $collection->getChildrenDisplayConfiguration()->getColumns())
                ;
            } else {
                // Else only preload datum used for ordering
                $qb
                    ->leftJoin('child.data', 'data', 'WITH', 'data.label = :label OR data IS NULL')
                ;
            }

            $results = $asArray ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();

            return array_map(static function ($result) use ($asArray) {
                $child = $result[0];
                if ($asArray) {
                    $child['orderingValue'] = $result['orderingValue'];
                } else {
                    $child->setOrderingValue($result['orderingValue']);
                }

                return $child;
            }, $results);
        }

        $qb = $this
            ->createQueryBuilder('child')
            ->where('child.parent = :parent')
            ->setParameter('parent', $collection->getId())
        ;

        if (DisplayModeEnum::DISPLAY_MODE_LIST === $collection->getChildrenDisplayConfiguration()->getDisplayMode()) {
            $qb
                ->addSelect('data')
                ->leftJoin('child.data', 'data', 'WITH', 'data.label IN (:labels) OR data IS NULL')
                ->setParameter('labels', $collection->getChildrenDisplayConfiguration()->getColumns())
            ;
        }

        return $asArray ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
    }
}
