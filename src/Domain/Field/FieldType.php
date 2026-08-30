<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Field;

enum FieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case EMAIL = 'email';
    case URL = 'url';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case INTEGER = 'integer';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case OBJECT = 'object';
    case ARRAY = 'array';

    public function restType(): string
    {
        return match ($this) {
            self::INTEGER => 'integer',
            self::NUMBER => 'number',
            self::BOOLEAN => 'boolean',
            self::OBJECT => 'object',
            self::ARRAY => 'array',
            default => 'string',
        };
    }
}
