<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Attributes;

#[\Attribute]
final class Constant
{
    public function __construct(
        private string $alias = ''
    ) {
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
