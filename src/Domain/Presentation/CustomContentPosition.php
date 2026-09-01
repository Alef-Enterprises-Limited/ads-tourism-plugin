<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Presentation;

enum CustomContentPosition: string
{
    case BEFORE = 'before';
    case AFTER = 'after';
    case TEMPLATE_SLOT = 'template_slot';

    public static function fromStoredValue(mixed $value): self
    {
        return is_string($value) ? self::tryFrom($value) ?? self::AFTER : self::AFTER;
    }
}
