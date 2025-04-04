<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TransferRequest
{
    #[Assert\NotBlank]
    public int $sourceAccountId;

    #[Assert\NotBlank]
    public int $targetAccountId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public string $amount;

    #[Assert\NotBlank]
    #[Assert\Currency]
    public string $currency;
}
