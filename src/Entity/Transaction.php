<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use App\Structure\transferStructure;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    public const STATUS_PENDING = 'pending';
    public const string STATUS_DONE = 'done';
    public const string STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Account $fromAccount = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Account $toAccount = null;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'decimal', precision: 15, scale: 2)]
    private string $convertedAmount;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column(length: 10)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getFromAccount(): Account
    {
        return $this->fromAccount;
    }

    public function setFromAccount(Account $fromAccount): self
    {
        $this->fromAccount = $fromAccount;

        return $this;
    }

    public function getToAccount(): Account
    {
        return $this->toAccount;
    }

    public function setToAccount(Account $toAccount): self
    {
        $this->toAccount = $toAccount;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConvertedAmount(): string
    {
        return $this->convertedAmount;
    }

    public function setConvertedAmount(string $amount): self
    {
        $this->convertedAmount = $amount;

        return $this;
    }

    public static function createFromStructure(transferStructure $structure): Transaction
    {
        return (new self())
            ->setAmount($structure->request->amount)
            ->setConvertedAmount($structure->convertedSourceAmount)
            ->setCurrency($structure->request->currency)
            ->setFromAccount($structure->sourceAccount)
            ->setToAccount($structure->targetAccount)
            ->setStatus(Transaction::STATUS_PENDING)
            ->setCreatedAt(new \DateTimeImmutable());
    }
}
