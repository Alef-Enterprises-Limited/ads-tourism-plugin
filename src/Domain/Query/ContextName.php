<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Query;

use InvalidArgumentException;

final readonly class ContextName
{
    public const MAX_LENGTH = 64;

    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if (
            $normalized === ''
            || strlen($normalized) > self::MAX_LENGTH
            || preg_match('/^[A-Za-z0-9_-]+$/', $normalized) !== 1
        ) {
            throw new InvalidArgumentException(
                'A shortcode context must contain only letters, numbers, hyphens, and underscores.',
            );
        }

        $this->value = $normalized;
    }

    public function parameter(string $name): string
    {
        return 'ads_' . strtolower($this->value) . '_' . $name;
    }
}
