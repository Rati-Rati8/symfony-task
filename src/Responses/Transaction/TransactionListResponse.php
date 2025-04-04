<?php

namespace App\Responses\Transaction;

readonly class TransactionListResponse
{
    public function __construct(
        /** @var TransactionResponse[] $data */
        public array $data,
        public int   $offset,
        public int   $limit,
        public int   $total
    )
    {
    }
}
