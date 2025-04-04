<?php

namespace App\Interfaces;

use App\Structure\transferStructure;

interface TransferValidatorInterface
{
    public function validate(transferStructure $transfer): void;
}
