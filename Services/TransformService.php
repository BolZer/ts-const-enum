<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Services;

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Annotations\TSConstant;
use PhpConstToTsConst\Configuration\Config;

class TransformService
{
    public function __construct(
        private Config $config,
        private ClassLoader $classLoader
    ){
    }

    public function transformAnnotatedConstantsToTypescriptCode(): array
    {
        $constants = $this->collectAnnotatedConstantsFromClassLoader();

    }

    /** @return \ReflectionClassConstant[] */
    private function collectAnnotatedConstantsFromClassLoader(): array
    {
        $buffer = [];

        $annotationReader = new ConstantAnnotationReader();

        foreach ($this->readRegularExpressionFromConfigFileToMatchRelevantClasses() as $className => $_classPath) {
            $reflectionClass = new \ReflectionClass($className);

            $constantWithTsConstantAnnotation = array_filter(\array_map(static function (\ReflectionClassConstant $reflectionClassConstant) use ($annotationReader) {
                if($annotationReader->getConstantAnnotation($reflectionClassConstant, TSConstant::class)){
                    return $reflectionClassConstant;
                }

                return null;
            }, $reflectionClass->getReflectionConstants()));

            if(!$constantWithTsConstantAnnotation){
                continue;
            }

            $buffer = \array_merge($buffer, $constantWithTsConstantAnnotation);
        }

        return $buffer;
    }

    /**
     * @return Array<string, string>
     * @psalm-suppress MixedReturnStatement, MixedInferredReturnType
     */
    private function readRegularExpressionFromConfigFileToMatchRelevantClasses(): array
    {
        if (!$this->config->getExcludeClassRegex()) {
            return $this->classLoader->getClassMap();
        }

        $buffer = [];
        foreach ($this->classLoader->getClassMap() as $class => $classPath) {
            $regexResults = \array_map(static fn (string $regex) => (bool)preg_match($regex, $class), $this->config->getExcludeClassRegex());

            if (\in_array(true, $regexResults, true)) {
                continue;
            }

            $buffer[$class] = $classPath;
        }

        return $buffer;
    }
}
