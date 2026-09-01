<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Query;

enum PaginationMode: string
{
    case NUMBERED = 'numbered';
    case PREVIOUS_NEXT = 'previous_next';
    case LOAD_MORE = 'load_more';
    case INFINITE = 'infinite';
    case NONE = 'none';
}
