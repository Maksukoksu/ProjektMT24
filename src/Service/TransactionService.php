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
    private TransactionRepository $taskRepository;
    private PaginatorInterface $paginator;
    private WalletService $walletService;
    private CategoryServiceInterface $categoryService;
    private TagService $tagService;

    public function __construct(TransactionRepository $taskRepository, PaginatorInterface $paginator, WalletService $walletService, CategoryServiceInterface $categoryService, TagService $tagService)
    {
        $this->taskRepository = $taskRepository;
        $this->paginator = $paginator;
        $this->walletService = $walletService;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
    }

    public function getPaginatedList(int $page, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->taskRepository->queryNotAll($filters),
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

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

    public function delete(Transaction $task): void
    {
        $this->taskRepository->delete($task);
    }

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
