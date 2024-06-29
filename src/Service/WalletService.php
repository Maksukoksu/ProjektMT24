<?php
/**
 * Wallet service.
 */

namespace App\Service;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Repository\TransactionRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Class WalletService.
 */
class WalletService implements WalletServiceInterface
{
    /**
     * Constructor.
     *
     * @param WalletRepository      $walletRepository      Wallet repository
     * @param PaginatorInterface    $paginator             Paginator
     * @param TransactionRepository $transactionRepository Transaction repository
     */
    public function __construct(private readonly WalletRepository $walletRepository, private readonly PaginatorInterface $paginator, private readonly TransactionRepository $transactionRepository)
    {
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
        $result = $this->transactionRepository->countByWallet($wallet);

        return $result <= 0;
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
    public function canAcceptTransaction(Wallet $wallet, float $transactionAmount, ?float $originalTransactionAmount = null): bool
    {
        $balance = $wallet->getBalance();

        // Pobranie i sprawdzenie stanu konta
        if (null !== $originalTransactionAmount) {
            $balance += abs($originalTransactionAmount);
        }

        // Check czy stan jest poniżej zera (ten pobrany stan)
        if ($transactionAmount < 0) {
            return ($balance - abs($transactionAmount)) >= 0;
        }

        // Jesli pobrane dane się zgadzają, transakja prechodzi
        return true;
    }

    /**
     * Update the balance of a wallet based on a transaction amount.
     *
     * @param Wallet $wallet The wallet to update
     * @param float  $amount The amount of the transaction
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

    /**
     * Find transactions for wallet by date range.
     *
     * @param Wallet                  $wallet   Wallet entity
     * @param \DateTimeInterface|null $dateFrom Start date
     * @param \DateTimeInterface|null $dateTo   End date
     *
     * @return array Transactions
     */
    public function findTransactionsForWalletByDateRange(Wallet $wallet, ?\DateTimeInterface $dateFrom, ?\DateTimeInterface $dateTo): array
    {
        return $this->transactionRepository->findTransactionsForWalletByDateRange($wallet, $dateFrom, $dateTo);
    }
}
