<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Commerce;

enum CommerceMode: string
{
    case CATALOGUE = 'catalogue';
    case ENQUIRY = 'enquiry';
    case WOOCOMMERCE = 'woocommerce';

    public static function fromStoredValue(mixed $value): self
    {
        return self::tryFrom(is_string($value) ? $value : '') ?? self::CATALOGUE;
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::CATALOGUE->value => 'Catalogue',
            self::ENQUIRY->value => 'Enquiry',
            self::WOOCOMMERCE->value => 'WooCommerce',
        ];
    }
}
