<?php

namespace PhpConstToTsConst\Tests;

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Configuration\Config;
use PhpConstToTsConst\Services\TransformService;
use PhpConstToTsConst\Tests\Fixtures\AnnotationTestFixture;
use PhpConstToTsConst\Tests\Traits\MockTrait;
use PhpConstToTsConst\Tests\Traits\ProphecyTrait;
use PHPUnit\Framework\TestCase;

class TransformServiceTest extends TestCase
{
    use MockTrait;

    public function testCollectAnnotatedConstantsFromClassLoader(): void
    {
        $service = new TransformService(new Config(), $this->getMockedClassLoader());

        $method = new \ReflectionMethod(TransformService::class, "collectAnnotatedConstantsFromClassLoader");
        $method->setAccessible(true);

        $result = $method->invoke($service);

        static::assertCount(2, $result);
    }
}