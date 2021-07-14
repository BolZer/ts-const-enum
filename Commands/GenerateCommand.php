<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Commands;

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Configuration\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    public function __construct(
        private Config $config,
        private ClassLoader $classLoader
    ) {
        parent::__construct('generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generateTypescriptCodeFromConstantsAndWriteToFiles($output);

        return 1;
    }

    private function generateTypescriptCodeFromConstantsAndWriteToFiles(OutputInterface $output): void
    {
        $tempBuffer = [];

        foreach ($this->readRegularExpressionFromConfigFileToMatchRelevantClasses() as $className => $_classPath) {
            try {
                if (!\class_exists($className)) {
                    continue;
                }
            } catch (\Throwable $throwable) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($className);

            /** @psalm-suppress TooManyArguments */
            $publicConstants = $reflectionClass->getReflectionConstants(\ReflectionClassConstant::IS_PUBLIC);

            if (!$publicConstants) {
                continue;
            }

            $scalarClassConstants = \array_filter($publicConstants, static fn (\ReflectionClassConstant $reflectionClassConstant) => \is_scalar($reflectionClassConstant->getValue()));

            foreach ($scalarClassConstants as $constantReflection) {
                $tempBuffer = $this->generateConstantAndAddToBuffer($tempBuffer, $constantReflection);
            }

            if (!$this->config->isShouldGenerateEnums()) {
                continue;
            }

            $scalarConstantMap = $this->createScalarConstantMap($scalarClassConstants);

            foreach (\array_filter($publicConstants, static fn (\ReflectionClassConstant $reflectionClassConstant) => \is_array($reflectionClassConstant->getValue())) as $constantReflection) {
                $tempBuffer = $this->generateEnumAndAddToBuffer($tempBuffer, $constantReflection, $scalarConstantMap);
            }
        }

        $this->writeContentToConstantFile($tempBuffer);
        $output->writeln(\sprintf('<info>Dumbed %s constants to file.</info>', \count($tempBuffer)));
    }

    private function generateConstantAndAddToBuffer(array $buffer, \ReflectionClassConstant $constantReflection): array
    {
        $scalarConstantName = $this->createConstantNameFromNamespaceAndConstantName($constantReflection);
        $buffer[$scalarConstantName] = $this->createTypescriptCodeForScalarConstant($scalarConstantName, $constantReflection->getValue());
        return $buffer;
    }

    /** @param Array<array-key, mixed> $scalarConstantMap */
    private function generateEnumAndAddToBuffer(array $overallBuffer, \ReflectionClassConstant $constantReflection, array $scalarConstantMap): array
    {
        $arrayConstantValue = $constantReflection->getValue();

        if (!\is_array($arrayConstantValue)) {
            return $overallBuffer;
        }

        if ($this->isMultiDimensionalArray($arrayConstantValue)) {
            return $overallBuffer;
        }

        $valueInMapResult = \array_map(static fn (mixed $value) => \in_array($value, $scalarConstantMap, true), $arrayConstantValue);

        if (\in_array(false, $valueInMapResult, true)) {
            return $overallBuffer;
        }

        $buffer = [];

        foreach ($arrayConstantValue as $value) {
            $key = \array_search($value, $scalarConstantMap, true);

            if ($key === false && \is_string($value)) {
                $buffer[] = \sprintf("'%s' = %s", $value, \var_export($value, true));
            } elseif ($key === false) {
                continue;
            } else {
                $buffer[] = \sprintf("'%s' = %s", $key, \var_export($value, true));
            }
        }

        $enumConstantName = $this->createConstantNameFromNamespaceAndConstantName($constantReflection);
        $overallBuffer[$enumConstantName] = \sprintf('export enum %s { %s }', $enumConstantName, \implode(', ', $buffer));
        return $overallBuffer;
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

    private function createConstantNameFromNamespaceAndConstantName(\ReflectionClassConstant $reflectionClassConstant): string
    {
        $declaringClassNamespace = $reflectionClassConstant->getDeclaringClass()->getNamespaceName();

        if ($declaringClassNamespace === '') {
            return $reflectionClassConstant->getName();
        }

        return \sprintf('%s_%s', \str_replace('\\', '__', $declaringClassNamespace), $reflectionClassConstant->getName());
    }

    private function writeContentToConstantFile(array $fileContent): void
    {
        $filePath = $this->config->getOutputPath();

        $dir = dirname($filePath);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        if (\file_exists($filePath)) {
            \unlink($filePath);
        }

        if ($this->config->isShouldAddGenerateHint()) {
            \array_unshift($fileContent, ...$this->getHeaderOfGeneratedFile());
        }

        file_put_contents($filePath, implode(\PHP_EOL, $fileContent));
    }

    /**
     * @param \ReflectionClassConstant[] $reflectionClassConstants
     * @return Array<array-key, mixed>
     */
    private function createScalarConstantMap(array $reflectionClassConstants): array
    {
        $buffer = [];

        foreach ($reflectionClassConstants as $reflectionClassConstant) {
            $buffer[$reflectionClassConstant->getName()] = $reflectionClassConstant->getValue();
        }

        return $buffer;
    }

    private function isMultiDimensionalArray(array $array): bool
    {
        return count(array_filter($array, static fn (mixed $value) => \is_array($value))) > 0;
    }

    private function getHeaderOfGeneratedFile(): array
    {
        return [
            '/*',
            'This Code was auto generated by the PHPConstAndPatchWorkEnumsToTypeScript Command',
            'Do not touch this code under any circumstances. Just run make',
            'php bin/php_const_to_typescript if you want to refresh this file',
            '*/',
        ];
    }

    /**
     * @return Array<string, string>
     * @psalm-suppress MixedReturnStatement, MixedInferredReturnType
     */
    private function readRegularExpressionFromConfigFileToMatchRelevantClasses(): array
    {
        if(!$this->config->getExcludeClassRegex()){
            return $this->classLoader->getClassMap();
        }

        $buffer = [];
        foreach($this->classLoader->getClassMap() as $class => $classPath){
            $regexResults = \array_map(static fn (string $regex) => (bool)preg_match($regex, $class), $this->config->getExcludeClassRegex());

            if(\in_array(true, $regexResults, true)){
                continue;
            }

            $buffer[$class] = $classPath;
        }

        return $buffer;
    }
}
