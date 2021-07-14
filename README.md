# PHP-CONST-TO-TYPESCRIPT

## Description

This tool will transform PHP Scalar-Constants - which are public - to Typescript Constants and generates Typescript Enums from PHP Makeshift-Enums.
This is useful if you want to reference a value in a condition. You can easily import the generated constants. This has
many advantages over just stating the value. If the value of the constant changes, your code won't break easily. 
If you change the name of a php constant the tyepscript code won't compile unless you changed all occurrences of the old
constant name.

This is especially useful if you use alot of JS/TS-Frameworks and do conditionally rendering in regards to 
values / properties. The Constants are prefixed with the namespace of where the constants resides to easily identify
then in your IDE and to ensure tree-shaking with webpack and co.

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
composer require bolzer/php-const-to-typescript
```

2. Create in your root (where the vendor folder is) dir a config file
```shell
touch .php-const-to-ts-config.php
```

3. Add the following content to the previously created file with the output path.
```php
<?php declare(strict_types=1);

use PhpConstToTsConst\Configuration\Config;

return (new Config())
    ->setOutputPath(__DIR__ . '/generated/constants.ts')
;
```

4. Take a look at the config class for more config options
5. Run the binary of the tool
```shell
php ./vendor/bin/php-const-to-typescript.php generate
```
6. Start using the constants

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