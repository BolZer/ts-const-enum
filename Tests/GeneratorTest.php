<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Tests;

use Bolzer\TsConstEnum\Configuration\Config;
use Bolzer\TsConstEnum\Services\Generator;
use Bolzer\TsConstEnum\Tests\Traits\MockTrait;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    use MockTrait;

    public function testCollectAnnotatedConstantsFromClassLoader(): void
    {
        $service = new Generator(new Config(), $this->getMockedClassLoader());

        $method = new \ReflectionMethod(Generator::class, 'collectAnnotatedConstantsFromClassLoader');
        $method->setAccessible(true);

        $result = $method->invoke($service);

        static::assertCount(3, $result);
    }

    public function testGenerate(): void
    {
        $service = new Generator(new Config(), $this->getMockedClassLoader());
        $result = $service->generate();
        static::assertCount(3, $result);

        $test = 0;
    }
}
