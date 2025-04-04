<?php

namespace App\Validator\Transfer;

use App\Structure\transferStructure;

readonly class TransferValidatorRunner
{
    public function __construct(
        private iterable $validators
    ) {}

    public function validate(transferStructure $transfer): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($transfer);
        }
    }
}
