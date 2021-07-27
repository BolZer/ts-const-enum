<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Tests\Fixtures;

use Bolzer\TsConstEnum\Attributes\Constant;
use Bolzer\TsConstEnum\Attributes\Enum;

class AnnotationTestFixture
{
    #[Constant]
    public const TEST_VALUE = 'mixed';

    #[Enum]
    public const TEST_ARRAY = [
        self::TEST_VALUE => 'another_value',
    ];

    #[Enum(alias: 'TestName')]
    public const TEST_SINGULAR_ARRAY = [
        'another_value',
    ];

    public const TEST_ARRAY_WITHOUT_ANNOTATION = [
        self::TEST_VALUE => 'another_value',
    ];
}
