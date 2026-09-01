<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Presentation;

enum LayoutMode: string
{
    case STANDARD = 'standard';
    case STANDARD_CUSTOM = 'standard_custom';
    case FULL_CUSTOM = 'full_custom';

    public static function fromStoredValue(mixed $value): self
    {
        return is_string($value) ? self::tryFrom($value) ?? self::STANDARD : self::STANDARD;
    }

    public function includesStructuredContent(): bool
    {
        return $this !== self::FULL_CUSTOM;
    }

    public function includesCustomContent(): bool
    {
        return $this !== self::STANDARD;
    }
}
