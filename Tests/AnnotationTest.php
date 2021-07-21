<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Tests;

use PhpConstToTsConst\Annotations\TSConstant;
use PhpConstToTsConst\Services\ConstantAnnotationReader;
use PhpConstToTsConst\Tests\Fixtures\AnnotationTestFixture;
use PHPUnit\Framework\TestCase;

class AnnotationTest extends TestCase
{
    public function testTSConstAnnotationAndReader(): void
    {
        $fixtureReflection = new \ReflectionClass(AnnotationTestFixture::class);

        $fixtureConstantsReflection = $fixtureReflection->getReflectionConstants();

        static::assertCount(3, $fixtureConstantsReflection);

        $annotationReader = new ConstantAnnotationReader();

        $constantWithTsConstantAnnotation = array_filter(\array_map(static function (\ReflectionClassConstant $reflectionClassConstant) use ($annotationReader) {
            if($annotationReader->getConstantAnnotation($reflectionClassConstant, TSConstant::class)){
                return $reflectionClassConstant;
            }

            return null;
        }, $fixtureConstantsReflection));

        static::assertCount(2, $constantWithTsConstantAnnotation);
    }
}
