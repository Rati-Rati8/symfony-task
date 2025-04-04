<?php

namespace App\Service\Assembler;

use App\Dto\TransferRequest;
use App\Entity\Account;
use App\Interfaces\CurrencyConversionServiceInterface;
use App\Structure\transferStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class TransferAssembler
{
    public function __construct(
        private EntityManagerInterface $em,
        private CurrencyConversionServiceInterface $conversionService)
    {
    }

    public function convertAndAssembleTransferStructure(TransferRequest $request): transferStructure
    {
        $repo = $this->em->getRepository(Account::class);

        $source = $repo->find($request->sourceAccountId);
        $target = $repo->find($request->targetAccountId);

        if (!$source) {
            throw new BadRequestHttpException('Source account not found.');
        }

        if (!$target) {
            throw new BadRequestHttpException('Target account not found.');
        }

        if ($source === $target) {
            throw new BadRequestHttpException('Cannot transfer to the same account.');
        }

        $converted = $this->conversionService->convertAmount(
            $request->amount,
            $target->getCurrency(),
            $source->getCurrency(),
        );

        $transferStructure = new transferStructure($request, $source, $target);
        $transferStructure->convertedSourceAmount = $converted;

        return $transferStructure;
    }
}
