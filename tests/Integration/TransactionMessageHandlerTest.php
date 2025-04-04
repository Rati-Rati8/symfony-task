<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Transaction;
use App\Exceptions\TransactionException;
use App\Factory\TransactionFactory;
use App\Factory\AccountFactory;
use App\Message\TransferFundsMessage;
use App\MessageHandler\TransferFundsMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TransactionMessageHandlerTest extends KernelTestCase
{
    use ResetDatabase,Factories;
    private EntityManagerInterface $em;
    private TransferFundsMessageHandler $handler;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->handler = self::getContainer()->get(TransferFundsMessageHandler::class);
    }

    public function testTransactionProcessingUpdatesBalancesAndSetsStatusToDone(): void
    {
        $client = new Client();
        $client->setName('Test Client');
        $this->em->persist($client);

        $source = AccountFactory::new(['balance' => '500.00'])->create();
        $target = AccountFactory::new(['balance' => '100.00'])->create();

        $transaction = TransactionFactory::new([
            'fromAccount' => $source,
            'toAccount' => $target,
            'amount' => '50.00',
            'convertedAmount' => '50.00',
            'status' => Transaction::STATUS_PENDING,
        ])->create();

        ($this->handler)(new TransferFundsMessage($transaction->getId()));

        $this->assertSame(Transaction::STATUS_DONE, $transaction->getStatus());
        $this->assertEquals(450.00, $source->getBalance());
        $this->assertEquals(150.00, $target->getBalance());
    }

    /**
     * @throws ORMException
     */
    public function testTransactionProcessingMakesErrorStatus_IfInsufficientBalance(): void
    {
        $client = new Client();
        $client->setName('Test Client');
        $this->em->persist($client);

        $source = AccountFactory::new(['balance' => '500.00'])->create();
        $target = AccountFactory::new(['balance' => '100.00'])->create();

        $transaction = TransactionFactory::new([
            'fromAccount' => $source,
            'toAccount' => $target,
            'amount' => '450.00',
            'convertedAmount' => '530.00',
            'status' => Transaction::STATUS_PENDING,
        ])->create();

        try {
            ($this->handler)(new TransferFundsMessage($transaction->getId()));
        } catch (TransactionException $e) {
            $this->assertSame('Insufficient balance, account:' . $source->getId(), $e->getMessage());
        }

        $this->assertSame(Transaction::STATUS_ERROR, $transaction->getStatus());
        $this->assertEquals(500.00, $source->getBalance());
        $this->assertEquals(100.00, $target->getBalance());
    }

    public function testTransactionProcessingThrowsException_IfInsufficientBalance(): void
    {
        $client = new Client();
        $client->setName('Test Client');
        $this->em->persist($client);

        $source = AccountFactory::new(['balance' => '500.00'])->create();
        $target = AccountFactory::new(['balance' => '100.00'])->create();

        $transaction = TransactionFactory::new([
            'fromAccount' => $source,
            'toAccount' => $target,
            'amount' => '450.00',
            'convertedAmount' => '530.00',
            'status' => Transaction::STATUS_PENDING,
        ])->create();

        $this->expectException(TransactionException::class);

        ($this->handler)(new TransferFundsMessage($transaction->getId()));
    }
}
