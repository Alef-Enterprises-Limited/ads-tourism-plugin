<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final class LocationCsvSchema
{
    public const VERSION = '1.0';

    /** @var list<string> */
    private const HEADERS = [
        'record_type',
        'external_id',
        'label',
        'latitude',
        'longitude',
        'role',
        'is_primary',
        'show_on_map',
        'sort_order',
    ];

    /** @return list<string> */
    public function headers(): array
    {
        return self::HEADERS;
    }
}
