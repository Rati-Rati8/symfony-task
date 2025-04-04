<?php

namespace App\Controller\Api;

use App\Service\ClientAccountService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/clients')]
class ClientController extends AbstractController
{
    #[Route('/{id}/accounts', name: 'api_client_accounts', methods: ['GET'])]
    public function getAccounts(int $id, ClientAccountService $service): JsonResponse
    {
        $accounts = $service->getClientAccounts($id);

        return $this->json($accounts);
    }
}
