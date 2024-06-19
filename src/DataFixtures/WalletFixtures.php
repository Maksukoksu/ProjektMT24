<?php
/**
 * Wallet fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Wallet;

/**
 * Class WalletFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class WalletFixtures extends AbstractBaseFixtures
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(20, 'wallet', function (int $i) {
            $wallet = new Wallet();
            $wallet->setType($this->faker->unique()->word);
            $wallet->setBalance($this->faker->randomDigitNotNull());
            $wallet->setTitle($this->faker->unique()->word);

            return $wallet;
        });

        $this->manager->flush();
    }
}
