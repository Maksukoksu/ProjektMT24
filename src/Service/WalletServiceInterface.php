<?php
/**
 * Wallet service interface.
 */

namespace App\Service;

use App\Entity\Wallet;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface WalletServiceInterface.
 */
interface WalletServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function save(Wallet $wallet): void;

    /**
     * Delete entity.
     *
     * @param Wallet $wallet Wallet entity
     */
    public function delete(Wallet $wallet): void;

    /**
     * Can Wallet be deleted?
     *
     * @param Wallet $wallet Wallet entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Wallet $wallet): bool;

    /**
     * Can Wallet accept transaction?
     *
     * @param Wallet     $wallet                    Wallet
     * @param float      $transactionAmount         Transaction Amount
     * @param float|null $originalTransactionAmount Original Transaction Amount
     *
     * @return bool Result
     */
    public function canAcceptTransaction(Wallet $wallet, float $transactionAmount, ?float $originalTransactionAmount = null): bool;
}
