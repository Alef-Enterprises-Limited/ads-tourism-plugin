<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Relationship\Relationship;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipSide;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;

final class InMemoryRelationshipRepository implements RelationshipRepository
{
    /** @var list<Relationship> */
    private array $relationships = [];

    public function replaceForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
        array $relationships,
    ): void {
        $this->relationships = array_values(array_filter(
            $this->relationships,
            static function (Relationship $relationship) use ($postId, $relationType, $side): bool {
                if ($relationship->type !== $relationType) {
                    return true;
                }

                return $side === RelationshipSide::SOURCE
                    ? $relationship->sourcePostId !== $postId
                    : $relationship->targetPostId !== $postId;
            },
        ));
        $this->relationships = [...$this->relationships, ...$relationships];
    }

    public function findForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
    ): array {
        $matches = array_filter(
            $this->relationships,
            static function (Relationship $relationship) use ($postId, $relationType, $side): bool {
                if ($relationship->type !== $relationType) {
                    return false;
                }

                return $side === RelationshipSide::SOURCE
                    ? $relationship->sourcePostId === $postId
                    : $relationship->targetPostId === $postId;
            },
        );
        usort(
            $matches,
            static fn(Relationship $left, Relationship $right): int => [!$left->isPrimary, $left->sortOrder]
                <=> [!$right->isPrimary, $right->sortOrder],
        );

        return array_values($matches);
    }

    public function deleteForPost(int $postId): int
    {
        $before = count($this->relationships);
        $this->relationships = array_values(array_filter(
            $this->relationships,
            static fn(Relationship $relationship): bool => $relationship->sourcePostId !== $postId
                && $relationship->targetPostId !== $postId,
        ));

        return $before - count($this->relationships);
    }

    public function deleteOrphans(): int
    {
        return 0;
    }
}
