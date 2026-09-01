<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

enum DuplicatePolicy: string
{
    case SKIP = 'skip';
    case UPDATE = 'update';
    case CREATE_NEW = 'create_new';
}
