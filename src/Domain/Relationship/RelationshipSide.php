<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Relationship;

enum RelationshipSide: string
{
    case SOURCE = 'source';
    case TARGET = 'target';
}
