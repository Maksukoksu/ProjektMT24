<?php
/**
 * Transaction fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Wallet;
// use App\Entity\Enum\TaskStatus;
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

        $this->createMany(100, 'tasks', function (int $i) {
            $task = new Transaction();
            $task->setTitle($this->faker->sentence);
            $task->setAmount($this->faker->randomDigitNotNull());
            $task->setBalanceAfterTransaction($this->faker->randomDigitNotNull());

            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $task->setCategory($category);
            /** @var Wallet $wallet */
            $wallet = $this->getRandomReference('wallet');
            $task->setWallet($wallet);

            return $task;
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
