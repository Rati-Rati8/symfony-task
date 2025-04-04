<?php

namespace App\Validator\Transfer;

use App\Interfaces\TransferValidatorInterface;
use App\Structure\transferStructure;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class BalanceValidator implements TransferValidatorInterface
{
    public function validate(transferStructure $transfer): void
    {
        if ((float) $transfer->sourceAccount->getBalance() < (float) $transfer->convertedSourceAmount) {
            throw new BadRequestHttpException('Insufficient funds in source account.');
        }
    }
}
