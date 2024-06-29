<?php
/**
 * Transaction repository.
 */

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Transaction;
use App\Entity\Wallet;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * Class TransactionRepository.
 *
 * This class handles the data layer for the Transaction entity.
 */
class TransactionRepository extends ServiceEntityRepository
{
    public const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The manager registry
     */
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
     * Delete a transaction.
     *
     * @param Transaction $transaction Transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->_em->remove($transaction);
        $this->_em->flush();
    }

    /**
     * Query not all records.
     *
     * @param array<string, mixed> $filters Filters array
     *
     * @return QueryBuilder Query builder
     */
    public function queryNotAll(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('transaction')
            ->join('transaction.wallet', 'wallet')
            ->addSelect('wallet');

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

    /**
     * Find transactions for a specific wallet within a date range.
     *
     * @param Wallet         $wallet   Wallet entity
     * @param \DateTime|null $dateFrom Start date of the range
     * @param \DateTime|null $dateTo   End date of the range
     *
     * @return Transaction[] List of transactions
     */
    public function findTransactionsForWalletByDateRange(Wallet $wallet, ?\DateTime $dateFrom, ?\DateTime $dateTo): array
    {
        $qb = $this->createQueryBuilder('transaction')
            ->where('transaction.wallet = :wallet')
            ->setParameter('wallet', $wallet);

        if ($dateFrom instanceof \DateTime) {
            $qb->andWhere('transaction.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom->format('Y-m-d'));
        }

        if ($dateTo instanceof \DateTime) {
            $qb->andWhere('transaction.createdAt <= :dateTo')
                ->setParameter('dateTo', $dateTo->format('Y-m-d'));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Count transactions by wallet.
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return int Number of transactions
     */
    public function countByWallet(Wallet $wallet): int
    {
        try {
            return (int) $this->createQueryBuilder('transaction')
                ->select('COUNT(transaction.id)')
                ->where('transaction.wallet = :wallet')
                ->setParameter('wallet', $wallet)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }
}
