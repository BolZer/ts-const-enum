<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Services;

use Bolzer\TsConstEnum\Attributes\Constant;
use Bolzer\TsConstEnum\Attributes\Enum;
use Bolzer\TsConstEnum\Configuration\Config;
use Bolzer\TsConstEnum\Dto\ConstantCollectResult;
use Bolzer\TsConstEnum\Traits\Assert;
use Composer\Autoload\ClassLoader;

class Generator
{
    use Assert;

    public function __construct(
        private Config $config,
        private ClassLoader $classLoader
    ) {
    }

    public function generate(): array
    {
        $constants = $this->collectAnnotatedConstantsFromClassLoader();

        $result = [];

        foreach ($constants as $constant) {
            if ($constant->getAnnotationClass() === Constant::class) {
                [$name, $code] = $this->generateCodeAndNameForAnnotatedConstant($constant->getReflectionClassConstant());

                if (isset($result[$name])) {
                    throw new \InvalidArgumentException(\sprintf('Constant with the name: %s already exists. Occurred for constant %s', $name, $constant->getReflectionClassConstant()->getName()));
                }

                $result[$name] = $code;
                continue;
            }

            if ($constant->getAnnotationClass() === Enum::class) {
                [$name, $code] = $this->generateCodeAndNameForAnnotatedEnum($constant->getReflectionClassConstant());

                if (isset($result[$name])) {
                    throw new \InvalidArgumentException(\sprintf('Constant with the name: %s already exists. Occurred for constant %s', $name, $constant->getReflectionClassConstant()->getName()));
                }

                $result[$name] = $code;
                continue;
            }

            throw new \InvalidArgumentException(
                \printf(
                    'Result with a different annotation class encountered. Type: %s not supported for %s',
                    $constant->getAnnotationClass(),
                    $constant->getReflectionClassConstant()->getName()
                )
            );
        }

        return $result;
    }

    /** @return string[] */
    private function generateCodeAndNameForAnnotatedConstant(\ReflectionClassConstant $reflectionClassConstant): array
    {
        $this->assertScalarReflectionConstant($reflectionClassConstant);

        return [
            $constantName = $this->createConstantNameFromReflection($reflectionClassConstant),
            $this->createTypescriptCodeForScalarConstant($constantName, $reflectionClassConstant->getValue()),
        ];
    }

    /** @return string[] */
    private function generateCodeAndNameForAnnotatedEnum(\ReflectionClassConstant $reflectionClassConstant): array
    {
        $this->assertEnumReflectionConstant($reflectionClassConstant);

        $buffer = [];

        foreach ($reflectionClassConstant->getValue() as $key => $value) {
            $key = \is_string($key) ? $key : $value;
            $buffer[] = \sprintf("'%s' = %s", (string)$key, \var_export($value, true));
        }

        return [
            $constantName = $this->createConstantNameFromReflection($reflectionClassConstant),
            \sprintf('export enum %s { %s }', $constantName, \implode(', ', $buffer)),
        ];
    }

    private function createConstantNameFromReflection(\ReflectionClassConstant $reflectionClassConstant): string
    {
        $result = \array_merge(
            $reflectionClassConstant->getAttributes(Constant::class),
            $reflectionClassConstant->getAttributes(Enum::class),
        );

        if (\count($result) !== 1) {
            throw new \InvalidArgumentException(\sprintf('Annotation of %s is invalid.', $reflectionClassConstant->getName()));
        }

        /** @var \ReflectionAttribute $attribute */
        $attribute = $result[0];

        $name = $attribute->getArguments()['alias'] ?? '';

        if ($name !== '') {
            return $name;
        }

        return \sprintf(
            '%s__%s',
            $reflectionClassConstant->getDeclaringClass()->getShortName(),
            $reflectionClassConstant->getName()
        );
    }

    private function createTypescriptCodeForScalarConstant(string $typeScriptConstantName, mixed $constantValue): string
    {
        $typeScriptCode = \sprintf('export const %s: %s = %s;', $typeScriptConstantName, 'placeholder_constant_type', "'placeholder_constant_value'");
        $strReplaceArguments = ['placeholder_constant_type', "'placeholder_constant_value'"];

        if (\is_string($constantValue)) {
            return \str_replace($strReplaceArguments, ['string', ($this->resolveScalarTypeValue($constantValue))], $typeScriptCode);
        }

        if (\is_numeric($constantValue)) {
            return \str_replace($strReplaceArguments, ['number', $this->resolveScalarTypeValue($constantValue)], $typeScriptCode);
        }

        if (\is_null($constantValue)) {
            return \str_replace($strReplaceArguments, 'null', $typeScriptCode);
        }

        if (\is_bool($constantValue)) {
            return \str_replace($strReplaceArguments, ['boolean', $this->resolveScalarTypeValue($constantValue)], $typeScriptCode);
        }

        throw new \InvalidArgumentException(\sprintf('Could not match constant %s', $typeScriptConstantName));
    }

    private function resolveScalarTypeValue(mixed $scalarValue): string
    {
        if (\is_string($scalarValue)) {
            return \var_export(addslashes(str_replace(["\n", "\t", "\r"], '', $scalarValue)), true);
        }

        if (\is_numeric($scalarValue)) {
            return var_export($scalarValue, true);
        }

        if (\is_null($scalarValue)) {
            return 'null';
        }

        if (\is_bool($scalarValue)) {
            return var_export($scalarValue, true);
        }

        throw new \InvalidArgumentException('Value did not meet the checks');
    }

    /** @return ConstantCollectResult[] */
    private function collectAnnotatedConstantsFromClassLoader(): array
    {
        $buffer = [];

        foreach ($this->classLoader->getClassMap() as $className => $_classPath) {
            try {
                $reflectionClass = new \ReflectionClass($className);
            } catch (\Throwable $throwable) {
                continue;
            }

            if ($reflectionClass->isAnonymous()) {
                continue;
            }

            $annotatedClassConstantReflections = array_filter(\array_map(static function (\ReflectionClassConstant $reflectionClassConstant) {
                if ($reflectionClassConstant->getAttributes(Constant::class)) {
                    return new ConstantCollectResult(
                        $reflectionClassConstant,
                        Constant::class
                    );
                }

                if ($reflectionClassConstant->getAttributes(Enum::class)) {
                    return new ConstantCollectResult(
                        $reflectionClassConstant,
                        Enum::class
                    );
                }

                return null;
            }, $reflectionClass->getReflectionConstants()));

            if (!$annotatedClassConstantReflections) {
                continue;
            }

            $buffer = \array_merge($buffer, $annotatedClassConstantReflections);
        }

        return \array_unique($buffer);
    }
}
