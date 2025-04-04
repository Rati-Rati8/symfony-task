<?php

namespace App\Validator;

use App\Dto\TransferRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class DtoValidatorService
{
    public function __construct(private ValidatorInterface $validator) {}

    public function validate(TransferRequest $dto): void
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            throw new BadRequestHttpException((string) $errors);
        }
    }
}
