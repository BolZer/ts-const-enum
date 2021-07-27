<?php

declare(strict_types=1);

namespace Bolzer\TsConstEnum\Configuration;

use Bolzer\TsConstEnum\Contracts\SelfValidationInterface;
use Bolzer\TsConstEnum\Traits\SelfValidationTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Config implements SelfValidationInterface
{
    use SelfValidationTrait;

    public const FILENAME = '.ts-const-enum-config.php';

    /** @Assert\NotBlank() */
    private string $outputPath = '';

    private bool $shouldAddGenerateHint = true;

    /** @var string[] */
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

    public function isShouldAddGenerateHint(): bool
    {
        return $this->shouldAddGenerateHint;
    }

    public function setShouldAddGenerateHint(bool $shouldAddGenerateHint): Config
    {
        $this->shouldAddGenerateHint = $shouldAddGenerateHint;
        return $this;
    }
}
