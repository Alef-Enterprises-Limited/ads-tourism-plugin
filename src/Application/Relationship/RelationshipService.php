<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\Relationship;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipSide;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use InvalidArgumentException;

final readonly class RelationshipService
{
    public function __construct(
        private RelationshipRepository $repository,
        private RecordTypeResolver $recordTypes,
    ) {}

    /**
     * @param list<int> $relatedPostIds
     */
    public function replace(
        int $postId,
        RelationType $relationType,
        array $relatedPostIds,
        ?int $primaryPostId = null,
    ): void {
        $recordType = $this->requireRecordType($postId);
        $recordSide = $relationType->sideFor($recordType);

        if ($recordSide === null) {
            throw new InvalidArgumentException('The record type is not valid for this relationship.');
        }

        $relatedPostIds = array_values(array_unique(array_filter(
            array_map(static fn(int $relatedPostId): int => max(0, $relatedPostId), $relatedPostIds),
            static fn(int $relatedPostId): bool => $relatedPostId > 0 && $relatedPostId !== $postId,
        )));
        $relationships = [];

        foreach ($relatedPostIds as $sortOrder => $relatedPostId) {
            $relatedType = $this->requireRecordType($relatedPostId);

            if (!in_array($relatedType, $relationType->counterpartTypes($recordType), true)) {
                throw new InvalidArgumentException('A selected record has an invalid type for this relationship.');
            }

            $sourcePostId = $recordSide === RelationshipSide::SOURCE ? $postId : $relatedPostId;
            $targetPostId = $recordSide === RelationshipSide::TARGET ? $postId : $relatedPostId;
            $relationships[] = new Relationship(
                $sourcePostId,
                $targetPostId,
                $relationType,
                $relationType->allowsPrimary() && $relatedPostId === $primaryPostId,
                $sortOrder,
            );
        }

        $this->repository->replaceForRecord($postId, $relationType, $recordSide, $relationships);
    }

    /**
     * @return list<Relationship>
     */
    public function find(int $postId, RelationType $relationType): array
    {
        $recordType = $this->requireRecordType($postId);
        $recordSide = $relationType->sideFor($recordType);

        if ($recordSide === null) {
            return [];
        }

        return $this->repository->findForRecord($postId, $relationType, $recordSide);
    }

    /**
     * @return list<int>
     */
    public function relatedPostIds(int $postId, RelationType $relationType): array
    {
        $recordType = $this->requireRecordType($postId);
        $recordSide = $relationType->sideFor($recordType);

        if ($recordSide === null) {
            return [];
        }

        return array_map(
            static fn(Relationship $relationship): int => $relationship->relatedPostId($recordSide),
            $this->repository->findForRecord($postId, $relationType, $recordSide),
        );
    }

    private function requireRecordType(int $postId): ContentType
    {
        $recordType = $this->recordTypes->resolve($postId);

        if ($recordType === null) {
            throw new InvalidArgumentException('The selected post is not an ADS Tourism record.');
        }

        return $recordType;
    }
}
