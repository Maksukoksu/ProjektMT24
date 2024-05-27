<?php
/**
 * Wallet service.
 */

namespace App\Service;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class WalletService.
 */
class WalletService implements WalletServiceInterface
{
    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    /**
     * Wallet repository.
     */
    private WalletRepository $walletRepository;

    /**
     * Transaction repository.
     */
    private TransactionRepository $taskRepository;

    /**
     * Constructor.
     *
     * @param WalletRepository      $walletRepository Wallet repository
     * @param PaginatorInterface    $paginator        Paginator
     * @param TransactionRepository $taskRepository   Transaction repository
     */
    public function __construct(WalletRepository $walletRepository, PaginatorInterface $paginator, TransactionRepository $taskRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->paginator = $paginator;
        $this->taskRepository = $taskRepository;
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->walletRepository->queryAll(),
            $page,
            TransactionRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /**
     * Can Wallet be deleted?
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Wallet $wallet): bool
    {
        try {
            $result = $this->taskRepository->countByWallet($wallet);

            return !($result > 0);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * Can Wallet accept transaction?
     *
     * @param Wallet     $wallet                    Wallet
     * @param float      $transactionAmount         TransactionAmount
     * @param float|null $originalTransactionAmount OriginalTransactionAmount
     *
     * @return bool Result
     */
    public function canAcceptTransaction(Wallet $wallet, float $transactionAmount, float $originalTransactionAmount = null): bool
    {
        $balance = $wallet->getBalance();

        // If originalTransactionAmount is provided, add it back to the balance
        if (null !== $originalTransactionAmount) {
            $balance += abs($originalTransactionAmount);
        }

        // Only check balance if the transaction amount is negative (a withdrawal).
        if ($transactionAmount < 0) {
            return ($balance - abs($transactionAmount)) >= 0;
        }

        // If the transaction amount is positive (a deposit), it's always acceptable.
        return true;
    }

    /**
     * Update the balance of a wallet based on a transaction amount.
     *
     * @param Wallet $wallet the wallet to update
     * @param float  $amount the amount of the transaction
     */
    public function updateBalance(Wallet $wallet, float $amount): void
    {
        $wallet->setBalance($wallet->getBalance() + $amount);
        $this->walletRepository->save($wallet);
    }

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void
    {
        $this->walletRepository->save($wallet);
    }

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void
    {
        $this->walletRepository->delete($wallet);
    }
}
