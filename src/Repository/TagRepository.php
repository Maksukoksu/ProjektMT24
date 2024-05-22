<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * Class TagRepository
 * @package App\Repository
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    /**
     * TagRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * Find tags by title.
     *
     * @param string $title
     * @return Tag[]
     */
    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->orderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find tags created after a specific date.
     *
     * @param \DateTime $date
     * @return Tag[]
     */
    public function findByCreatedAfter(\DateTime $date): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.createdAt > :date')
            ->setParameter('date', $date)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the number of tags.
     *
     * @return int
     */
    public function countTags(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Base query builder for reusable queries.
     *
     * @return QueryBuilder
     */
    private function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.title', 'ASC');
    }
}