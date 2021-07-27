<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Tests\Traits;

use Bolzer\TsConstEnum\Tests\Fixtures\AnnotationTestFixture;
use Composer\Autoload\ClassLoader;
use Prophecy\Prophet;

trait MockTrait
{
    public function getMockedClassLoader(): ClassLoader
    {
        $fixtureReflection = new \ReflectionClass(AnnotationTestFixture::class);

        $classLoaderMock = (new Prophet())->prophesize(ClassLoader::class);
        $classLoaderMock->getClassMap()->willReturn([
            $fixtureReflection->getName() => $fixtureReflection->getFileName(),
        ]);

        return $classLoaderMock->reveal();
    }
}
