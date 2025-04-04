<?php
namespace App\Structure;

use App\Dto\TransferRequest;
use App\Entity\Account;

class transferStructure
{
    public function __construct(
        public readonly TransferRequest $request,
        public readonly Account         $sourceAccount,
        public readonly Account         $targetAccount,
        public ?string  $convertedSourceAmount = null,
    ){}
}
