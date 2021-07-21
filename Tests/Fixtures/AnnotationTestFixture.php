<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Tests\Fixtures;

use PhpConstToTsConst\Annotations\TSConstant;

class AnnotationTestFixture
{
    /** @TSConstant() */
    public const TEST_VALUE = 'mixed';

    /** @TSConstant() */
    public const TEST_ARRAY = [
        self::TEST_VALUE => 'another_value',
    ];

    public const TEST_ARRAY_WITHOUT_ANNOTATION = [
        self::TEST_VALUE => 'another_value',
    ];
}
