<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Transaction;

class TransactionRepository extends ServiceEntityRepository
{
const PAGINATOR_ITEMS_PER_PAGE = 10; // Define the constant

public function __construct(ManagerRegistry $registry)
{
parent::__construct($registry, Transaction::class);
}

/**
* Save a transaction.
*
* @param Transaction $transaction Transaction entity
*/
public function save(Transaction $transaction): void
{
$this->_em->persist($transaction);
$this->_em->flush();
}

/**
* Query not all records.
*
* @param array<string, mixed> $filters Filters array
*
* @return \Doctrine\ORM\QueryBuilder Query builder
*/
public function queryNotAll(array $filters = []): \Doctrine\ORM\QueryBuilder
{
$qb = $this->createQueryBuilder('transaction')
->select('transaction');

if (!empty($filters['category'])) {
$qb->andWhere('transaction.category = :category')
->setParameter('category', $filters['category']);
}

if (!empty($filters['tag'])) {
$qb->join('transaction.tags', 'tag')
->andWhere('tag.id = :tag')
->setParameter('tag', $filters['tag']);
}

return $qb;
}
}
