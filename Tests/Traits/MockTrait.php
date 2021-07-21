<?php

namespace Traits;

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Tests\Fixtures\AnnotationTestFixture;
use Prophecy\Prophet;

namespace PhpConstToTsConst\Tests\Traits;

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Tests\Fixtures\AnnotationTestFixture;
use Prophecy\Prophet;

trait MockTrait
{
    public function getMockedClassLoader(): ClassLoader
    {
        $fixtureReflection = new \ReflectionClass(AnnotationTestFixture::class);

        $classLoaderMock = (new Prophet())->prophesize(ClassLoader::class);
        $classLoaderMock->getClassMap()->willReturn([
            $fixtureReflection->getName() => $fixtureReflection->getFileName()
        ]);

        return $classLoaderMock->reveal();
    }
}