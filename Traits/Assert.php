<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Traits;

trait Assert
{
    public function assertScalarReflectionConstant(\ReflectionClassConstant $reflectionClassConstant): void
    {
        if (!\is_scalar($reflectionClassConstant->getValue())) {
            throw new \InvalidArgumentException(\sprintf('Constant %s is not scalar. The @Constant annotation is not supported', $reflectionClassConstant->getName()));
        }
    }

    public function assertEnumReflectionConstant(\ReflectionClassConstant $reflectionClassConstant): void
    {
        $value = $reflectionClassConstant->getValue();

        if (!\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Constant %s is not an array. The @Enum annotation is not supported', $reflectionClassConstant->getName()));
        }

        if (count(array_filter($value, static fn (mixed $value) => \is_array($value))) > 0) {
            throw new \InvalidArgumentException(\sprintf('Constant %s is multi dimensional. The @Enum annotation is not supported', $reflectionClassConstant->getName()));
        }
    }
}
