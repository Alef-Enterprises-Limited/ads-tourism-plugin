<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Relationship;

final readonly class Relationship
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public int $sourcePostId,
        public int $targetPostId,
        public RelationType $type,
        public bool $isPrimary = false,
        public int $sortOrder = 0,
        public array $metadata = [],
        public ?int $id = null,
    ) {}

    public function relatedPostId(RelationshipSide $recordSide): int
    {
        return $recordSide === RelationshipSide::SOURCE
            ? $this->targetPostId
            : $this->sourcePostId;
    }
}
