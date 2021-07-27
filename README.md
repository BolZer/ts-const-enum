# Ts-Const-Enum (PHP)

## Description

This tool will transform PHP Scalar-Constants - which are annotated with the provided attributes - to Typescript Constants and generates Typescript 
Enums from annotated PHP one dimensional constant arrays. This may be useful if you want to reference a value in a condition within your Typescript Code. You can easily import the generated constants. This has
many advantages over just stating the value. If the value of the constant changes, your code won't break easily. 
If you change the name of a php constant the typescript code won't compile unless you changed all occurrences of the old
constant name.

This is especially useful if you use alot of JS/TS-Frameworks and do conditionally rendering in regards to 
values / properties. The Constants are by default prefixed with the declaring class followed by __ and the 
constant name. You may provide an alias on attribute-level. Keep in mind to have unique names / alias as the
constants will land in one file.

### Usecase

#### Before
```Typescript
if (reflection.type === 16) {
    // Do something when true
}
```

### After
```Typescript
import {TARGET_CLASS_CONSTANT} from "./constants";

if (reflection.type === TARGET_CLASS_CONSTANT) {
    // Do something when true
}
```

# Installation

1. Require the dependency

```shell
composer require bolzer/ts-const-enum
```

2. Create in your root (where the vendor folder is) dir a config file
```shell
touch .ts-const-enum-config.php
```

3. Add the following content to the previously created file with the output path.
```php
<?php declare(strict_types=1);

use Bolzer\TsConstEnum\Configuration\Config;

return (new Config())
    ->setOutputPath(__DIR__ . '/generated/constants.ts')
;
```

4. Take a look at the config class for more config option.
5. Annotate some constants and arrays in your php code with the provided attributes `Constant` and `Enum`

**Example**

```php
<?php declare(strict_types=1);

namespace Test\Example;

use Bolzer\TsConstEnum\Attributes\Constant;use Bolzer\TsConstEnum\Attributes\Enum;

class ExampleClass {
    #[Constant(alias: "Test")]
    private const TEST = "test";
    
    #[Enum]
    private const TEST_2 = [
        self::TEST => "value"
    ];
}
```

6. Run the binary of the tool
```shell
composer dump-autoload -o --quiet
php ./vendor/bin/ts-const-enum.php generate
```
7. Start using the constants

## What's a makeshift enum in PHP?
Currently there're no enums in PHP. This will change with the release of PHP 8.2.
However, those enums need to be transformed to typescript enums too. In the meantime
i use constructs like these:

```php
class Membership {
    public const FREE = "free";
    public const PREMIUM = "premium";
    
    public const TYPES = [
        self::FREE,
        self::PREMIUM
    ];  
}
```

The constant "TYPES" is a makeshift enum. Those constructs will be transformed too. This will translate to the following enum
in Typescript

```Typescript
export enum Membership_TYPES { 
    'FREE' = 'free', 
    'PREMIUM' = 'premium',
}
```

## Stability
The tool is in active development. Therefore you need to add this repository in your composer.json. 
There's currently no packagist package available. There is also the chance of bugs.