<?php
namespace App\Entity;

use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Tag;

/**
 * Class Transaction.
 *
 * @psalm-suppress MissingConstructor
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ORM\Table(name: 'transactions')]
class Transaction
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt;

    /**
     * Updated at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'EXTRA_LAZY', inversedBy: 'transactions')]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * Amount.
     */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotBlank]
    private ?string $amount = null;

    /**
     * Wallet.
     */
    #[ORM\ManyToOne(targetEntity: Wallet::class, fetch: 'EXTRA_LAZY', inversedBy: 'transactions')]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wallet $wallet = null;

    /**
     * BalanceAfterTransaction.
     */
    #[ORM\Column(type: Types::FLOAT)]
    private ?float $balanceAfterTransaction;

    /**
     * Tags.
     */
    private array $tags = [];

    /**
     * Setter for balanceAfterTransaction.
     *
     * @param float|null $balanceAfterTransaction BalanceAfterTransaction
     *
     * @return Transaction BalanceAfterTransaction
     */
    public function setBalanceAfterTransaction(?float $balanceAfterTransaction): self
    {
        $this->balanceAfterTransaction = $balanceAfterTransaction;

        return $this;
    }

    /**
     * Getter for balanceAfterTransaction.
     *
     * @return float|null BalanceAfterTransaction
     */
    public function getBalanceAfterTransaction(): ?float
    {
        return $this->balanceAfterTransaction;
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
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category|null $category Category
     *
     * @return Transaction Category
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for amount.
     *
     * @return string|null Amount
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * Setter for amount.
     *
     * @param string|null $amount Amount
     *
     * @return Transaction Amount
     */
    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Getter for wallet.
     *
     * @return Wallet|null Wallet
     */
    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    /**
     * Setter for wallet.
     *
     * @param Wallet|null $wallet Wallet
     *
     * @return Transaction Wallet
     */
    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return array<Tag> Tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Add a tag.
     *
     * @param Tag $tag Tag
     *
     * @return Transaction
     */
    public function addTag(Tag $tag): self
    {
        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * Remove a tag.
     *
     * @param Tag $tag Tag
     *
     * @return Transaction
     */
    public function removeTag(Tag $tag): self
    {
        if (($key = array_search($tag, $this->tags, true)) !== false) {
            unset($this->tags[$key]);
            $this->tags = array_values($this->tags);
        }

        return $this;
    }
}
