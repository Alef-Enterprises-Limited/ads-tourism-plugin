<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Shortcode;

enum ContextComponent: string
{
    case SEARCH = 'search';
    case FILTERS = 'filters';
    case SORT = 'sort';
    case RESULTS = 'results';
    case PAGINATION = 'pagination';
    case LISTING = 'listing';

    public function isPrimary(): bool
    {
        return $this === self::RESULTS || $this === self::LISTING;
    }
}
