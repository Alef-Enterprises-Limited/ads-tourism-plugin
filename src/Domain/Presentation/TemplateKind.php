<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Presentation;

enum TemplateKind: string
{
    case SINGLE = 'single';
    case ARCHIVE = 'archive';
    case TAXONOMY = 'taxonomy';
}
