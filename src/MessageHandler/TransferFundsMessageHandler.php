<?php

namespace App\MessageHandler;

use App\Entity\Transaction;
use App\Exceptions\TransactionException;
use App\Message\TransferFundsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class TransferFundsMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    /**
     * @throws TransactionException
     */
    public function __invoke(TransferFundsMessage $message): void
    {
        $transaction = $this->em->getRepository(Transaction::class)->find($message->transactionId);

        if (!$transaction) {
            throw new \Exception('transaction not found id: ' .  $transaction->getId());
        }

        $source = $transaction->getFromAccount();
        $target = $transaction->getToAccount();

        $sourceBalance = (float) $source->getBalance() - (float) $transaction->getConvertedAmount();
        $targetBalance = (float) $target->getBalance() + (float) $transaction->getAmount();

        if ($sourceBalance <= 0) {
            $transaction->setStatus(Transaction::STATUS_ERROR);
            $this->em->flush();
            throw new TransactionException('Insufficient balance, account:' . $source->getId());
        }

        $source->setBalance($sourceBalance);
        $target->setBalance($targetBalance);
        $transaction->setStatus(Transaction::STATUS_DONE);

        $this->em->flush();
    }
}
