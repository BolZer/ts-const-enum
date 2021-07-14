#!/usr/bin/env php
<?php declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use PhpConstToTsConst\Commands\GenerateCommand;
use PhpConstToTsConst\Configuration\Config;
use Symfony\Component\Console\Application;

$classLoader = getClassLoader();

if ($classLoader === null) {
    fwrite(STDERR, 'Could not require the composer class loader');
    return;
}

$config = getConfig();

if ($config === null) {
    fwrite(STDERR, 'Could not require config');
    return;
}

if (!$config->isValid()) {
    fwrite(STDERR, 'Loaded Configuration is invalid' . PHP_EOL);

    /** @var \Symfony\Component\Validator\ConstraintViolation[] $violations */
    $violations = $config->getViolations();

    foreach ($violations as $violation) {
        fwrite(STDERR, sprintf('%s: %s', $violation->getPropertyPath(), (string)$violation->getMessage()) . PHP_EOL);
    }

    return;
}

$consoleApplication = new Application();
$consoleApplication->setName('PHP-CONST-TO-TYPESCRIPT');
$consoleApplication->add(new GenerateCommand($config, $classLoader));
$consoleApplication->run();

function getClassLoader(): ?ClassLoader
{
    $autoloaderPaths = [
        __DIR__ . '/../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
    ];

    $existingAutoloaderPath = null;

    foreach ($autoloaderPaths as $autoloaderPath) {
        if (!file_exists($autoloaderPath)) {
            continue;
        }

        $existingAutoloaderPath = $autoloaderPath;
    }

    if ($existingAutoloaderPath === null) {
        fwrite(STDERR, 'Couldn\'t find autoloader path');
        return null;
    }

    /** @psalm-suppress UnresolvableInclude */
    $classLoader = require $existingAutoloaderPath;

    if (!$classLoader instanceof ClassLoader) {
        return null;
    }

    return $classLoader;
}

function getConfig(): ?Config
{
    $configPaths = [
        __DIR__ . '/../../' . Config::FILENAME,
        __DIR__ . '/../' . Config::FILENAME,
        __DIR__ . '/' . Config::FILENAME,
    ];

    $existingConfigPath = null;

    foreach ($configPaths as $configPath) {
        if (!file_exists($configPath)) {
            continue;
        }

        $existingConfigPath = $configPath;
    }

    if ($existingConfigPath === null) {
        return null;
    }

    /** @psalm-suppress UnresolvableInclude */
    $config = require $existingConfigPath;

    if (!$config instanceof Config) {
        return null;
    }

    return $config;
}
