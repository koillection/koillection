<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Collection;
use App\Entity\Item;
use App\Entity\Tag;
use App\Model\Search\Search;
use App\Model\Search\SearchTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findWithItems(string $id): ?Tag
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.items', 'i')
            ->addSelect('i')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    #[\Override]
    public function findAll(): array
    {
        return $this
            ->createQueryBuilder('t')
            ->orderBy('t.label', Criteria::ASC)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForTagSearch(SearchTag $search, string $context, int $itemsCount): array
    {
        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT t as tag')
            ->addSelect('count(DISTINCT i.id) as itemCount')
            ->addSelect('(count(DISTINCT i.id)*100.0/:totalItems) as percent')
            ->from(Tag::class, 't')
            ->leftJoin('t.items', 'i')
            ->groupBy('t.id')
            ->orderBy('itemCount', Criteria::DESC)
            ->addOrderBy('t.label', Criteria::ASC)
            ->setFirstResult(($search->getPage() - 1) * $search->getItemsPerPage())
            ->setMaxResults($search->getItemsPerPage())
            ->setParameter('totalItems', $itemsCount > 0 ? $itemsCount : 1)
        ;

        if ('shared' === $context) {
            $qb->having('count(i.id) > 0');
        }

        if ($search->getTerm() !== null && $search->getTerm() !== '') {
            $qb
                ->andWhere('LOWER(t.label) LIKE LOWER(:search)')
                ->setParameter('search', '%' . trim($search->getTerm()) . '%')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function countForTagSearch(SearchTag $search, string $context): int
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('count(DISTINCT t.id)')
            ->from(Tag::class, 't')
        ;

        if ('shared' === $context) {
            $qb
                ->innerJoin('t.items', 'i')
                ->having('count(i.id) > 1')
            ;
        }

        if ($search->getTerm() !== null && $search->getTerm() !== '') {
            $qb
                ->andWhere('LOWER(t.label) LIKE LOWER(:search)')
                ->setParameter('search', '%' . trim($search->getTerm()) . '%')
            ;
        }

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $noResultException) {
            return 0;
        }
    }

    public function findLike(string $string): array
    {
        $string = trim($string);

        return $this
            ->createQueryBuilder('t')
            ->addSelect('(CASE WHEN LOWER(t.label) LIKE LOWER(:startWith) THEN 0 ELSE 1 END) AS HIDDEN startWithOrder')
            ->andWhere('LOWER(t.label) LIKE LOWER(:label)')
            ->orderBy('startWithOrder', Criteria::ASC) // Order tags starting with the search term first
            ->addOrderBy('LOWER(t.label)', Criteria::ASC) // Then order other matching tags alphabetically
            ->setParameter('label', '%' . $string . '%')
            ->setParameter('startWith', $string . '%')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRelatedToCollection(Collection $collection): array
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.items', 'i')
            ->where('i.collection = :collection')
            ->orderBy('t.label', Criteria::ASC)
            ->groupBy('t.id')
            ->having('count(i.id) =
                (SELECT COUNT(i2.id)
                FROM App\Entity\Item i2
                WHERE i2.collection = :collection)')
            ->setParameter('collection', $collection->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForSearch(Search $search): array
    {
        $itemsCount = $this->getEntityManager()->getRepository(Item::class)->count([]);

        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('t as tag')
            ->addSelect('count(i.id) as itemCount')
            ->addSelect('(count(i.id)*100.0/:totalItems) as percent')
            ->from(Tag::class, 't')
            ->leftJoin('t.items', 'i')
            ->groupBy('t.id')
            ->orderBy('itemCount', Criteria::DESC)
            ->setParameter('totalItems', $itemsCount)
        ;

        if (\is_string($search->getTerm()) && $search->getTerm() !== '') {
            $qb
                ->andWhere('LOWER(t.label) LIKE LOWER(:term)')
                ->setParameter('term', '%' . $search->getTerm() . '%')
            ;
        }

        if ($search->getCreatedAt() instanceof \DateTimeImmutable) {
            $createdAt = $search->getCreatedAt();
            $qb
                ->andWhere('t.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $createdAt->setTime(0, 0, 0))
                ->setParameter('end', $createdAt->setTime(23, 59, 59))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllForHighlight(): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT t, LENGTH(t.label) as HIDDEN length')
            ->from(Tag::class, 't')
            ->orderBy('length', Criteria::DESC)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findRelatedTags(Tag $tag)
    {
        // Get all items ids the current tag is linked to
        $results = $this->getEntityManager()->createQueryBuilder()
            ->select('DISTINCT i2.id')
            ->from(Item::class, 'i2')
            ->leftJoin('i2.tags', 't2')
            ->where('t2.id = :tag')
            ->setParameter('tag', $tag->getId())
            ->getQuery()
            ->getArrayResult()
        ;

        $itemIds = array_map(static function ($row) {
            return $row['id'];
        }, $results);

        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('DISTINCT t')
            ->from(Tag::class, 't')
            ->leftJoin('t.items', 'i')
            ->where('i.id IN (:itemIds)')
            ->andWhere('t.id != :tag')
            ->orderBy('t.label', Criteria::ASC)
            ->setParameter('tag', $tag->getId())
            ->setParameter('itemIds', $itemIds)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getUnusedTags()
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.items', 'i')
            ->where('i.id IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
