<?php

namespace App\Responses\Transaction;

use App\Entity\Transaction;

readonly class TransactionResponse
{
    public int $id;
    public string $amount;
    public string $currency;
    public string $status;
    public int $from_account_id;
    public int $to_account_id;
    public string $created_at;

    public function __construct(Transaction $transaction)
    {
        $this->id = $transaction->getId();
        $this->amount = $transaction->getAmount();
        $this->currency = $transaction->getCurrency();
        $this->status = $transaction->getStatus();
        $this->from_account_id = $transaction->getFromAccount()->getId();
        $this->to_account_id = $transaction->getToAccount()->getId();
        $this->created_at = $transaction->getCreatedAt()->format(DATE_ATOM);
    }
}
