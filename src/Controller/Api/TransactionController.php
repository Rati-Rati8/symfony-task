<?php

namespace App\Controller\Api;

use App\Entity\Account;
use App\Service\TransactionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/transactions')]
class TransactionController extends AbstractController
{
    public function __construct(private readonly TransactionService $transactionService)
    {}

    #[Route('/create', name: 'api_transfer_funds', methods: ['POST'])]
    public function transfer(Request $request, TransactionService $service): JsonResponse
    {
        try {
            $transaction = $service->createTransactionMessage($request->getContent());
            return $this->json(['status' => 'transaction queued with id - ' . $transaction->getId()], 202);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/account/{id}/', name: 'account_transaction_history', methods: ['GET'])]
    public function getHistory(
        Account $account,
        Request $request,
    ): JsonResponse
    {
        try {
            $result = $this->transactionService->getHistory($request, $account);

            return $this->json([
                'data' => $result->data,
                'meta' => [
                    'offset' => $result->offset,
                    'limit' => $result->limit,
                    'total' => $result->total,
                ]
            ]);
        } catch (BadRequestHttpException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }
    }
}
