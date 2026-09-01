<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

enum ImportRunStatus: string
{
    case UPLOADED = 'uploaded';
    case VALIDATED = 'validated';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
