<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionHistoryRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    public int $offset = 0;

    #[Assert\NotNull]
    #[Assert\LessThanOrEqual(100)]
    public int $limit = 10;
}
