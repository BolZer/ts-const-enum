<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Dto;

class ConstantCollectResult
{
    public function __construct(
        private \ReflectionClassConstant $reflectionClassConstant,
        private string $annotationClass
    ) {
    }

    public function __toString(): string
    {
        return $this->reflectionClassConstant->getDeclaringClass()->getShortName() . '_' . $this->reflectionClassConstant->getName();
    }

    public function getReflectionClassConstant(): \ReflectionClassConstant
    {
        return $this->reflectionClassConstant;
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }
}
