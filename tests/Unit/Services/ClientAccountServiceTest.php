<?php
namespace App\Tests\Unit\Services;

use App\Entity\Account;
use App\Entity\Client;
use App\Service\ClientAccountService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientAccountServiceTest extends TestCase
{
    public function testGetClientAccountsReturnsFormattedData(): void
    {
        $account1 = $this->createMock(Account::class);
        $account1->method('getId')->willReturn(1);
        $account1->method('getCurrency')->willReturn('USD');
        $account1->method('getBalance')->willReturn('100.00');

        $account2 = $this->createMock(Account::class);
        $account2->method('getId')->willReturn(2);
        $account2->method('getCurrency')->willReturn('EUR');
        $account2->method('getBalance')->willReturn('50.00');

        $accountCollection = new ArrayCollection([$account1, $account2]);

        $client = $this->createMock(Client::class);
        $client->method('getAccounts')->willReturn($accountCollection);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->with(123)->willReturn($client);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Client::class)->willReturn($repo);

        $service = new ClientAccountService($em);
        $result = $service->getClientAccounts(123);

        $this->assertCount(2, $result);
        $this->assertSame([
            ['id' => 1, 'currency' => 'USD', 'balance' => '100.00'],
            ['id' => 2, 'currency' => 'EUR', 'balance' => '50.00'],
        ], $result);
    }

    /**
     * @throws Exception
     */
    public function testGetClientAccountsThrowsExceptionWhenClientNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->with(999)->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(Client::class)->willReturn($repo);

        $service = new ClientAccountService($em);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Client not found.');

        $service->getClientAccounts(999);
    }
}
