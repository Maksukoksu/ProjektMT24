<?php
/**
 * Wallet entity.
 */

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Wallet Transaction.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[ORM\Table(name: 'wallet')]
class Wallet
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Type.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $type = null;

    /**
     * Balance.
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\Type('float')]
    #[Assert\GreaterThanOrEqual(0.0, message: 'balance.min_value')]
    private ?float $balance = 0.0;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * ArrayCollection.
     */
    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: Transaction::class, fetch: 'EXTRA_LAZY')]
    private Collection $transactions;

    /**
     * Constructor for ArrayCollection.
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    /**
     * Getter for Collection.
     *
     * @return Collection Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for type.
     *
     * @return string|null Type
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Setter for type.
     *
     * @param string|null $type Type
     *
     * @return Wallet Type
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Getter for balance.
     *
     * @return float|null Balance
     */
    public function getBalance(): ?float
    {
        $balance = 0.0;
        foreach ($this->transactions as $transaction) {
            $balance += floatval($transaction->getAmount());
        }

        return $balance;
    }

    /**
     * Setter for balance.
     *
     * @param string|null $balance Balance
     *
     * @return Wallet Balance
     */
    public function setBalance(?string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string|null $title Title
     *
     * @return Wallet Title
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
