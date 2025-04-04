<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Account;
use App\Dto\TransferRequest;
use App\Structure\transferStructure;
use App\Validator\Transfer\CurrencyMatchValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CurrencyMatchValidatorTest extends TestCase
{
    public function testValidCurrencyPasses(): void
    {
        $request = new TransferRequest();
        $request->currency = 'USD';

        $targetAccount = (new Account())->setCurrency('USD');

        $structure = new transferStructure($request, new Account(), $targetAccount);

        $validator = new CurrencyMatchValidator();

        $this->expectNotToPerformAssertions();
        $validator->validate($structure);
    }

    public function testMismatchedCurrencyThrowsException(): void
    {
        $request = new TransferRequest();
        $request->currency = 'USD';

        $targetAccount = (new Account())->setCurrency('EUR');

        $structure = new transferStructure($request, new Account(), $targetAccount);

        $validator = new CurrencyMatchValidator();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Currency must match the target account currency.');

        $validator->validate($structure);
    }
}
