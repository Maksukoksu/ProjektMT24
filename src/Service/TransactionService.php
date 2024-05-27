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
 */
class TransactionService implements TransactionServiceInterface
{
    /**
     * Transaction repository.
     */
    private TransactionRepository $taskRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Wallet repository.
     */
    private WalletService $walletService;

    /**
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

    /**
     * Constructor.
     *
     * @param TransactionRepository    $taskRepository  Transaction repository
     * @param PaginatorInterface       $paginator       Paginator
     * @param WalletService            $walletService   Wallet service
     * @param CategoryServiceInterface $categoryService Category service
     */
    public function __construct(TransactionRepository $taskRepository, PaginatorInterface $paginator, WalletService $walletService, CategoryServiceInterface $categoryService)
    {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->walletService = $walletService;
        $this->categoryService = $categoryService;
    }

    /**
     * Get paginated list.
     *
     * @param int                $page    Page number
     * @param array<string, int> $filters Filters array
     *
     * @return PaginationInterface<string, mixed> Paginated list
     *
     * @throws NonUniqueResultException
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
     * Save entity.
     *
     * @param Transaction $task                      Transaction entity
     * @param float|null  $originalTransactionAmount Original Transaction Amount
     */
    public function save(Transaction $task, float $originalTransactionAmount = null): void
    {
        $this->walletService->updateBalance($task->getWallet(), $task->getAmount());
        $balance = $task->getWallet()->getBalance();
        if (null !== $originalTransactionAmount) {
            $task->setBalanceAfterTransaction($balance);
        } else {
            $task->setBalanceAfterTransaction($balance + $task->getAmount());
        }
        $this->taskRepository->save($task);
    }

    /**
     * Delete entity.
     *
     * @param Transaction $task Transaction entity
     */
    public function delete(Transaction $task): void
    {
        $this->taskRepository->delete($task);
    }

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     *
     * @throws NonUniqueResultException
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

        return $resultFilters;
    }
}
