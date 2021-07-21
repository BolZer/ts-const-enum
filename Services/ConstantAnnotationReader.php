<?php

namespace PhpConstToTsConst\Services;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationReader;

class ConstantAnnotationReader
{
    public function getConstantAnnotations(\ReflectionClassConstant $constant): array
    {
        $class   = $constant->getDeclaringClass();
        $context = 'const ' . $class->getName() . '::' . $constant->getName();

        $annotationReader = new AnnotationReader();

        $annotationParserClosure = \Closure::bind(static function(AnnotationReader $annotationReader) {
            return $annotationReader->parser;
        }, null, $annotationReader);


        $annotationParserImportClosure = \Closure::bind(static function(AnnotationReader $annotationReader, \ReflectionClass $reflectionClass) {
            return $annotationReader->getImports($reflectionClass);
        }, null, $annotationReader);

        $parser = $annotationParserClosure($annotationReader);

        $parser->setTarget(Target::TARGET_PROPERTY);
        $parser->setImports($annotationParserImportClosure($annotationReader, $constant->getDeclaringClass()));

        return $parser->parse($constant->getDocComment(), $context);
    }

    public function getConstantAnnotation(\ReflectionClassConstant $constant, string $annotationName): mixed
    {
        $annotations = $this->getConstantAnnotations($constant);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }
}