<?php

namespace App\Tests\Unit\Validator;

use App\Entity\Account;
use App\Dto\TransferRequest;
use App\Structure\transferStructure;
use App\Validator\Transfer\BalanceValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BalanceValidatorTest extends TestCase
{
    public function testSufficientBalancePasses(): void
    {
        $source = (new Account())->setCurrency('USD')->setBalance('100.00');
        $target = (new Account())->setCurrency('USD');

        $request = new TransferRequest();
        $request->currency = 'USD';
        $request->amount = '50.00';

        $structure = new transferStructure($request, $source, $target);
        $structure->convertedSourceAmount = '50.00';

        $validator = new BalanceValidator();

        $this->expectNotToPerformAssertions();
        $validator->validate($structure);
    }

    public function testInsufficientBalanceThrowsException(): void
    {
        $source = (new Account())->setCurrency('USD')->setBalance('30.00');
        $target = (new Account())->setCurrency('USD');

        $request = new TransferRequest();
        $request->currency = 'USD';
        $request->amount = '50.00';

        $structure = new transferStructure($request, $source, $target);
        $structure->convertedSourceAmount = '50.00';

        $validator = new BalanceValidator();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Insufficient funds in source account.');

        $validator->validate($structure);
    }
}

