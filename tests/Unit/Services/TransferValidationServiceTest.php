<?php

namespace App\Tests\Unit\Services;

use App\Interfaces\TransferValidatorInterface;
use App\Structure\transferStructure;
use App\Validator\Transfer\BalanceValidator;
use App\Validator\Transfer\CurrencyMatchValidator;
use App\Validator\Transfer\TransferValidatorRunner;
use App\Validator\TransferValidatorService;
use PHPUnit\Framework\TestCase;

class TransferValidationServiceTest extends TestCase
{
    public function testAllValidatorsAreExecuted(): void
    {
        $structure = $this->createMock(transferStructure::class);
        $validator1 = $this->createMock(BalanceValidator::class);
        $validator2 = $this->createMock(CurrencyMatchValidator::class);
        $validators = [$validator1, $validator2];

        foreach ($validators as $validator) {
            $validator->expects($this->once())
                ->method('validate')
                ->with($structure);
        }

        $runner = new TransferValidatorRunner([$validator1, $validator2]);
        $service = new TransferValidatorService($runner);

        $service->validatedTransfer($structure);
    }
}
