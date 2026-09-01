<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Query;

enum QuerySort: string
{
    case TITLE_ASC = 'title_asc';
    case TITLE_DESC = 'title_desc';
    case NEWEST = 'newest';
    case OLDEST = 'oldest';
    case MANUAL = 'manual';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case DURATION = 'duration';
    case RANDOM = 'random';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::TITLE_ASC->value => 'Title A–Z',
            self::TITLE_DESC->value => 'Title Z–A',
            self::NEWEST->value => 'Newest',
            self::OLDEST->value => 'Oldest',
            self::MANUAL->value => 'Manual order',
            self::PRICE_ASC->value => 'Price: low to high',
            self::PRICE_DESC->value => 'Price: high to low',
            self::DURATION->value => 'Duration',
            self::RANDOM->value => 'Random',
        ];
    }
}
