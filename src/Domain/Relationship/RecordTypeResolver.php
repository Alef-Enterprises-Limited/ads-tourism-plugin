<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

interface RecordTypeResolver
{
    public function resolve(int $postId): ?ContentType;
}
