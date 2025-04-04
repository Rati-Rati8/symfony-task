<?php

namespace App\Message;

final readonly class TransferFundsMessage
{
    public function __construct(
        public string $transactionId
    ) {}
}
