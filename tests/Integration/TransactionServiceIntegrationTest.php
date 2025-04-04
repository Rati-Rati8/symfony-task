<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Transaction;
use App\Factory\AccountFactory;
use App\Factory\ClientFactory;
use App\Message\TransferFundsMessage;
use App\Service\Client\FastForexClient;
use App\Service\CurrencyConversion\CurrencyConversionServiceCore;
use App\Service\TransactionService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TransactionServiceIntegrationTest extends KernelTestCase
{
    use ResetDatabase,Factories;
    private EntityManagerInterface $em;
    private TransactionService $service;

    protected function setUp(): void
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'results' => ['EUR' => 0.85, 'USD' => 1.0]
            ])),
        ]);
        $mockClient = new FastForexClient($mockHttpClient, 'fake-key');

        $mockBus = $this->createMock(MessageBusInterface::class);
        $mockBus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn($msg) => $msg instanceof TransferFundsMessage))
            ->willReturnCallback(fn($msg) => new Envelope($msg));

        self::ensureKernelShutdown();
        self::bootKernel([
            'environment' => 'test',
        ]);

        self::getContainer()->set(MessageBusInterface::class, $mockBus);
        self::getContainer()->set(FastForexClient::class, $mockClient);
        self::getContainer()->set(CurrencyConversionServiceCore::class, new CurrencyConversionServiceCore($mockClient));
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->service = self::getContainer()->get(TransactionService::class);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCreateTransactionMessage_CreatesTransactionWithPendingAndMessageIsDispatched(): void
    {
        $client = ClientFactory::createOne();

        $source = AccountFactory::new([
            'client' => $client,
            'balance' => '1000.00',
            'currency' => 'USD'
        ])->create();

        $target = AccountFactory::new([
            'client' => $client,
            'balance' => '100.00',
            'currency' => 'EUR'
        ])->create();

        $this->em->flush();

        $json = json_encode([
            'sourceAccountId' => $source->getId(),
            'targetAccountId' => $target->getId(),
            'amount' => '100.00',
            'currency' => 'EUR'
        ]);

        $transaction = $this->service->createTransactionMessage($json);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals('100.00', $transaction->getAmount());
        $this->assertEquals('EUR', $transaction->getCurrency());
        $this->assertEquals(Transaction::STATUS_PENDING, $transaction->getStatus());
        $this->assertEquals($source->getId(), $transaction->getFromAccount()->getId());
        $this->assertEquals($target->getId(), $transaction->getToAccount()->getId());
    }
}
