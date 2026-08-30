<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Relationship;

interface RelationshipRepository
{
    /**
     * @param list<Relationship> $relationships
     */
    public function replaceForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
        array $relationships,
    ): void;

    /**
     * @return list<Relationship>
     */
    public function findForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
    ): array;

    public function deleteForPost(int $postId): int;

    public function deleteOrphans(): int;
}
