<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

enum TaxonomyImportMode: string
{
    case SIMPLE = 'simple';
    case ADVANCED = 'advanced';
}
