<?php


namespace App\Validator\Transfer;

use App\Interfaces\TransferValidatorInterface;
use App\Structure\transferStructure;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CurrencyMatchValidator implements TransferValidatorInterface
{
    public function validate(transferStructure $transfer): void
    {
        if (strtoupper($transfer->request->currency) !== strtoupper($transfer->targetAccount->getCurrency())) {
            throw new BadRequestHttpException('Currency must match the target account currency.');
        }
    }
}
