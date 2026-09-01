<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

enum MediaUrlType: string
{
    case ABSOLUTE = 'absolute';
    case RELATIVE = 'relative';
}
