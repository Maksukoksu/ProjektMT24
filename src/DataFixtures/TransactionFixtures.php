<?php
/**
 * Transaction fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Wallet;
use App\Entity\Transaction;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TransactionFixtures.
 */
class TransactionFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(100, 'transactions', function (int $i) {
            $transaction = new Transaction();
            $transaction->setTitle($this->faker->sentence);
            $transaction->setAmount($this->faker->randomDigitNotNull());
            $transaction->setBalanceAfterTransaction($this->faker->randomDigitNotNull());

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $transaction->setCategory($category);
            /** @var Wallet $wallet */
            $wallet = $this->getRandomReference('wallet');
            $transaction->setWallet($wallet);

            return $transaction;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, WalletFixtures::class];
    }
}
