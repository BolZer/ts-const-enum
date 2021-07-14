<?php

declare(strict_types=1);

namespace PhpConstToTsConst\Configuration;

use PhpConstToTsConst\Contracts\SelfValidationInterface;
use PhpConstToTsConst\Traits\SelfValidationTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Config implements SelfValidationInterface
{
    use SelfValidationTrait;

    public const FILENAME = '.php-const-to-ts-config.php';

    /** @Assert\NotBlank() */
    private string $outputPath = '';

    private bool $shouldGenerateEnums = true;
    private bool $shouldAddGenerateHint = true;

    /** @var String[]  */
    private array $excludeClassRegex = [];

    public static function loadConstraints(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraints('outputPath', [
            new Assert\NotBlank(),
        ]);
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function setOutputPath(string $outputPath): self
    {
        $this->outputPath = $outputPath;
        return $this;
    }

    public function isShouldGenerateEnums(): bool
    {
        return $this->shouldGenerateEnums;
    }

    public function setShouldGeneratePatchworkEnums(bool $generateEnums): self
    {
        $this->shouldGenerateEnums = $generateEnums;
        return $this;
    }

    public function isShouldAddGenerateHint(): bool
    {
        return $this->shouldAddGenerateHint;
    }

    public function setShouldAddGenerateHint(bool $shouldAddGenerateHint): Config
    {
        $this->shouldAddGenerateHint = $shouldAddGenerateHint;
        return $this;
    }

    public function getExcludeClassRegex(): array
    {
        return $this->excludeClassRegex;
    }

    public function setExcludeForFullyQualifiedNamespaceRegexes(array $excludeClassRegex): Config
    {
        $this->excludeClassRegex = $excludeClassRegex;
        return $this;
    }
}
