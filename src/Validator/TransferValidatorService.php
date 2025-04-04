<?php
namespace App\Validator;

use App\Dto\TransferRequest;
use App\Structure\transferStructure;
use App\Validator\Transfer\TransferValidatorRunner;


readonly class TransferValidatorService
{
    public function __construct(
        private readonly TransferValidatorRunner $validatorRunner
    ) {}

    /**
     * Validate transfer request from raw JSON.
     *
     * @param transferStructure $transferStructure
     * @return transferStructure [TransferRequest $dto, Account $sourceAccount, Account $destinationAccount]
     */
    public function validatedTransfer(TransferStructure $transferStructure): transferStructure
    {
        $this->validatorRunner->validate($transferStructure);

        return $transferStructure;
    }
}
