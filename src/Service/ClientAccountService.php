<?php

namespace App\Service;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class ClientAccountService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getClientAccounts(int $clientId): array
    {
        $client = $this->em->getRepository(Client::class)->find($clientId);

        if (!$client) {
            throw new NotFoundHttpException("Client not found.");
        }

        return array_map(function ($account) {
            return [
                'id' => $account->getId(),
                'currency' => $account->getCurrency(),
                'balance' => $account->getBalance(),
            ];
        }, $client->getAccounts()->toArray());
    }
}
