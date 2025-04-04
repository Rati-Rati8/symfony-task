<?php

namespace App\Tests\Unit\Services;

use App\Dto\TransferRequest;
use App\Entity\Account;
use App\Service\Assembler\TransferAssembler;
use App\Service\CurrencyConversion\CurrencyConversionServiceLoggingDecorator;
use App\Structure\transferStructure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TransferAssemblerTest extends TestCase
{
    private TransferRequest $request;
    private Account $sourceAccount;
    private Account $targetAccount;

    protected function setUp(): void
    {
        $this->request = new TransferRequest();
        $this->request->sourceAccountId = 1;
        $this->request->targetAccountId = 2;
        $this->request->amount = '100.00';
        $this->request->currency = 'USD';

        $this->sourceAccount = (new Account())->setCurrency('USD')->setBalance('500.00');
        $this->targetAccount = (new Account())->setCurrency('USD')->setBalance('300.00');
    }

    public function testReturnsValidTransferStructure_WithCorrectData(): void
    {
        $repo = $this->createMock(EntityRepository::class);

        $repo->method('find')->willReturnCallback(function ($id) {
            return match ($id) {
                1 => $this->sourceAccount,
                2 => $this->targetAccount,
                default => null,
            };
        });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $conversion = $this->createMock(CurrencyConversionServiceLoggingDecorator::class);
        $conversion->method('convertAmount')->willReturn('100.0');

        $assembler = new TransferAssembler($em, $conversion);

        $result = $assembler->convertAndAssembleTransferStructure($this->request);

        $this->assertInstanceOf(transferStructure::class, $result);
        $this->assertSame($this->sourceAccount, $result->sourceAccount);
        $this->assertSame($this->targetAccount, $result->targetAccount);
        $this->assertSame('100.0', $result->convertedSourceAmount);
    }

    public function testThrowsExceptionWhenSourceNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturnCallback(fn($id) => $id === 2 ? $this->targetAccount : null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $conversion = $this->createMock(CurrencyConversionServiceLoggingDecorator::class);

        $assembler = new TransferAssembler($em, $conversion);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Source account not found.');
        $assembler->convertAndAssembleTransferStructure($this->request);
    }

    public function testThrowsExceptionWhenTargetNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturnCallback(fn($id) => $id === 1 ? $this->sourceAccount : null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $conversion = $this->createMock(CurrencyConversionServiceLoggingDecorator::class);

        $assembler = new TransferAssembler($em, $conversion);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Target account not found.');
        $assembler->convertAndAssembleTransferStructure($this->request);
    }

    public function testThrowsExceptionWhenAccountsAreSame(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($this->sourceAccount);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $conversion = $this->createMock(CurrencyConversionServiceLoggingDecorator::class);

        $assembler = new TransferAssembler($em, $conversion);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot transfer to the same account.');
        $assembler->convertAndAssembleTransferStructure($this->request);
    }
}
