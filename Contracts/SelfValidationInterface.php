<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Contracts;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

interface SelfValidationInterface
{
    public static function loadConstraints(ClassMetadata $metadata): void;

    public function isValid(): bool;

    public function getViolations(): ConstraintViolationListInterface;
}
