<?php

namespace App\Service;

use App\Dto\TransactionHistoryRequest;
use App\Dto\TransferRequest;
use App\Entity\Account;
use App\Entity\Transaction;
use App\Message\TransferFundsMessage;
use App\Repository\TransactionRepository;
use App\Responses\Transaction\TransactionListResponse;
use App\Responses\Transaction\TransactionResponse;
use App\Service\Assembler\TransferAssembler;
use App\Structure\transferStructure;
use App\Validator\DtoValidatorService;
use App\Validator\TransferValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class TransactionService
{
    public function __construct(
        private SerializerInterface       $serializer,
        private TransferValidatorService  $transferValidatorService,
        private MessageBusInterface        $bus,
        private DtoValidatorService $dtoValidator,
        private TransferAssembler $assembler,
        private EntityManagerInterface $entityManager,
        private TransactionRepository $transactionRepository,
        private ValidatorInterface $validator
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function createTransactionMessage(string $json): Transaction
    {
        $transferStructure = $this->validateJsonAndAssembleStructure($json);
        $transaction = Transaction::createFromStructure($transferStructure);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        $this->bus->dispatch(new TransferFundsMessage($transaction->getId()));

        return $transaction;
    }

    private function validateJsonAndAssembleStructure(string $json): transferStructure
    {
        $transferRequest = $this->serializer->deserialize($json, TransferRequest::class, 'json');
        $this->dtoValidator->validate($transferRequest);

        $transferStructure = $this->assembler->convertAndAssembleTransferStructure($transferRequest);
        $this->transferValidatorService->validatedTransfer($transferStructure);

        return $transferStructure;
    }

    public function getHistory(Request $request, Account $account): TransactionListResponse
    {
        $pagination = new TransactionHistoryRequest();
        $pagination->offset = (int) $request->query->get('offset', 0);
        $pagination->limit = (int) $request->query->get('limit', 10);

        $errors = $this->validator->validate($pagination);
        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }

        $transactions = $this->transactionRepository->findByAccount($account, $pagination->offset, $pagination->limit);
        $total = $this->transactionRepository->countByAccount($account);
        $data = array_map(fn($t) => new TransactionResponse($t), $transactions);

        return new TransactionListResponse($data, $pagination->offset, $pagination->limit, $total);
    }
}
