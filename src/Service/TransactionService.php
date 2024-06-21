<?php
/**
 * Transaction service.
 */

namespace App\Service;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class TransactionService.
 *
 * This service handles the business logic related to transactions.
 */
class TransactionService implements TransactionServiceInterface
{
    private TransactionRepository $taskRepository;
    private PaginatorInterface $paginator;
    private WalletService $walletService;
    private CategoryServiceInterface $categoryService;
    private TagService $tagService;

    /**
     * Constructor.
     *
     * @param TransactionRepository    $taskRepository  The transaction repository
     * @param PaginatorInterface       $paginator       The paginator service
     * @param WalletService            $walletService   The wallet service
     * @param CategoryServiceInterface $categoryService The category service
     * @param TagService               $tagService      The tag service
     */
    public function __construct(TransactionRepository $taskRepository, PaginatorInterface $paginator, WalletService $walletService, CategoryServiceInterface $categoryService, TagService $tagService) {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->walletService = $walletService;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }

    /**
     * Get paginated list of transactions.
     *
     * @param int   $page    The current page number
     * @param array $filters The filters to apply
     *
     * @return PaginationInterface The paginated list of transactions
     */
    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->taskRepository->queryNotAll($filters),
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Save a transaction.
     *
     * @param Transaction $task                      The transaction entity
     * @param float|null  $originalTransactionAmount The original transaction amount
     */
    public function save(Transaction $task, float $originalTransactionAmount = null): void
    {
        // Update the wallet balance
        $this->walletService->updateBalance($task->getWallet(), $task->getAmount());
        $balance = $task->getWallet()->getBalance();
        if (null !== $originalTransactionAmount) {
            $task->setBalanceAfterTransaction($balance);
        } else {
            $task->setBalanceAfterTransaction($balance + $task->getAmount());
        }

        // Save the transaction
        $this->taskRepository->save($task);
    }

    /**
     * Delete a transaction.
     *
     * @param Transaction $task The transaction entity
     */
    public function delete(Transaction $task): void
    {
        $this->taskRepository->delete($task);
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
            if (null !== $tag) {
                $resultFilters['tag'] = $tag;
            }
        }

        return $resultFilters;
    }
}
