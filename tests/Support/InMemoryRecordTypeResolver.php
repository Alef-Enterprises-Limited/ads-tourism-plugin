<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;

final readonly class InMemoryRecordTypeResolver implements RecordTypeResolver
{
    /**
     * @param array<int, ContentType> $recordTypes
     */
    public function __construct(private array $recordTypes) {}

    public function resolve(int $postId): ?ContentType
    {
        return $this->recordTypes[$postId] ?? null;
    }
}
