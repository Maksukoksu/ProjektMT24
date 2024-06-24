<?php
/**
 * Transaction service.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TransactionService.
 *
 * This service handles the business logic related to transactions.
 */
class TransactionService implements TransactionServiceInterface
{
    /**
     * Constructs the WalletController.
     *
     * @param TransactionRepository    $transactionRepository The repository for transactions
     * @param PaginatorInterface       $paginator             The paginator service
     * @param WalletService            $walletService         The service for managing wallets
     * @param CategoryServiceInterface $categoryService       The service for managing categories
     * @param TagService               $tagService            The service for managing tags
     */
    public function __construct(private readonly TransactionRepository $transactionRepository, private readonly PaginatorInterface $paginator, private readonly WalletService $walletService, private readonly CategoryServiceInterface $categoryService, private readonly TagService $tagService)
    {
    }

    /**
     * Get paginated list of transactions.
     *
     * @param int    $page      The current page number
     * @param array  $filters   The filters to apply
     * @param string $sortField The field to sort by
     * @param string $sortDir   The sort direction
     *
     * @return PaginationInterface The paginated list of transactions
     */
    public function getPaginatedList(int $page, array $filters = [], string $sortField = 'transaction.createdAt', string $sortDir = 'desc'): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        $queryBuilder = $this->transactionRepository->queryNotAll($filters)
            ->orderBy($sortField, $sortDir);

        return $this->paginator->paginate(
            $queryBuilder,
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save a transaction.
     *
     * @param Transaction $transaction               The transaction entity
     * @param float|null  $originalTransactionAmount The original transaction amount
     */
    public function save(Transaction $transaction, ?float $originalTransactionAmount = null): void
    {
        // Update the wallet balance
        $this->walletService->updateBalance($transaction->getWallet(), $transaction->getAmount());
        $balance = $transaction->getWallet()->getBalance();
        if (null !== $originalTransactionAmount) {
            $transaction->setBalanceAfterTransaction($balance);
        } else {
            $transaction->setBalanceAfterTransaction($balance + $transaction->getAmount());
        }

        // Save the transaction
        $this->transactionRepository->save($transaction);
    }

    /**
     * Delete a transaction.
     *
     * @param Transaction $transaction The transaction entity
     */
    public function delete(Transaction $transaction): void
    {
        $this->transactionRepository->delete($transaction);
    }

    /**
     * Prepare filters for querying transactions.
     *
     * @param array $filters The filters to prepare
     *
     * @return array The prepared filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        if (!empty($filters['tag_id'])) {
            $tag = $this->tagService->findOneById($filters['tag_id']);
            if ($tag instanceof \App\Entity\Tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}
