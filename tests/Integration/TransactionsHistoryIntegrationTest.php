<?php

namespace App\Tests\Integration;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Factory\AccountFactory;
use App\Factory\TransactionFactory;
use App\Responses\Transaction\TransactionResponse;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class TransactionsHistoryIntegrationTest extends KernelTestCase
{
    use ResetDatabase,Factories;
    private TransactionService $service;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->service = self::getContainer()->get(TransactionService::class);

        $this->loadTestData();
    }

    public function testPaginatedTransactionHistoryReturnsCorrectStructure(): void
    {
        $account = $this->em->getRepository(Account::class)->findOneBy(['currency' => 'USD']);
        $this->assertNotNull($account, 'Account not found');

        $cases = [
            [0, 10, 10],
            [25, 10, 5],
            [30, 10, 0],
        ];

        foreach ($cases as [$offset, $limit, $expectedCount]) {
            $request = new Request(query: ['offset' => $offset, 'limit' => $limit]);
            $result = $this->service->getHistory($request, $account);

            $this->assertCount($expectedCount, $result->data, "Failed for offset=$offset, limit=$limit");
            $this->assertSame($offset, $result->offset);
            $this->assertSame($limit, $result->limit);
            $this->assertSame(30, $result->total);

            if ($expectedCount > 0) {
                $this->assertInstanceOf(TransactionResponse::class, $result->data[0]);
            }
        }
    }

    private function loadTestData(): void
    {
        $account = AccountFactory::new()->create();

        TransactionFactory::new([
            'fromAccount' => $account,
            'toAccount' => $account,
            'status' => Transaction::STATUS_DONE,
        ])->many(30)->create();
    }
}
