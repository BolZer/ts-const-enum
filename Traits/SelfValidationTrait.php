<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

trait SelfValidationTrait
{
    public function getViolations(): ConstraintViolationListInterface
    {
        return Validation::createValidatorBuilder()
            ->addMethodMapping('loadConstraints')
            ->getValidator()
            ->validate($this)
        ;
    }

    public function isValid(): bool
    {
        return Validation::createValidatorBuilder()
            ->addMethodMapping('loadConstraints')
            ->getValidator()
            ->validate($this)
            ->count() === 0
        ;
    }
}
