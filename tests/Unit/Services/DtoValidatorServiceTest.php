<?php

namespace App\Tests\Unit\Services;

use App\Dto\TransferRequest;
use App\Validator\DtoValidatorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DtoValidatorServiceTest extends KernelTestCase
{
    public function testValidDtoPassesValidation(): void
    {
        self::bootKernel();

        $dto = new TransferRequest();
        $dto->sourceAccountId = 1;
        $dto->targetAccountId = 2;
        $dto->currency = 'USD';
        $dto->amount = '100.00';

        /** @var DtoValidatorService $service */
        $service = self::getContainer()->get(DtoValidatorService::class);
        $service->validate($dto);

        $this->expectNotToPerformAssertions();
        $service->validate($dto);
    }

    public function testInvalidDtoThrowsException(): void
    {
        self::bootKernel();

        $dto = new TransferRequest();

        /** @var DtoValidatorService $service */
        $service = self::getContainer()->get(DtoValidatorService::class);

        $this->expectException(BadRequestHttpException::class);
        $service->validate($dto);
    }
}
