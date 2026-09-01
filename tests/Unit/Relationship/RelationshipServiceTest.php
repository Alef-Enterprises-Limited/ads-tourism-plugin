<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Relationship;

use AlefDigitalSolutions\ADSTourism\Application\Relationship\RelationshipService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryRecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryRelationshipRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RelationshipServiceTest extends TestCase
{
    private InMemoryRelationshipRepository $repository;

    private RelationshipService $service;

    protected function setUp(): void
    {
        $this->repository = new InMemoryRelationshipRepository();
        $this->service = new RelationshipService(
            $this->repository,
            new InMemoryRecordTypeResolver([
                10 => ContentType::ACTIVITY,
                20 => ContentType::PLACE,
                21 => ContentType::PLACE,
                30 => ContentType::STAY,
                40 => ContentType::PACKAGE,
                50 => ContentType::OPERATOR,
            ]),
        );
    }

    public function testOneWriteCanBeQueriedFromBothDirections(): void
    {
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [20, 21]);

        self::assertSame(
            [20, 21],
            $this->service->relatedPostIds(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
        );
        self::assertSame(
            [10],
            $this->service->relatedPostIds(20, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
        );
    }

    public function testReplacementRemovesOnlyTheEditedSideAndRelation(): void
    {
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [20, 21]);
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [21]);

        self::assertSame(
            [21],
            $this->service->relatedPostIds(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
        );
        self::assertSame([], $this->service->relatedPostIds(20, RelationType::ACTIVITY_AVAILABLE_AT_PLACE));
    }

    public function testDuplicateSelectionsCreateOneCanonicalRelationship(): void
    {
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [20, 20, 20]);

        self::assertSame(
            [20],
            $this->service->relatedPostIds(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
        );
        self::assertSame(
            [10],
            $this->service->relatedPostIds(20, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
        );
    }

    public function testPrimaryProviderIsPersisted(): void
    {
        $this->service->replace(40, RelationType::PACKAGE_OFFERED_BY, [30, 50], 50);

        $relationships = $this->service->find(40, RelationType::PACKAGE_OFFERED_BY);
        self::assertTrue($relationships[0]->isPrimary);
        self::assertSame(50, $relationships[0]->targetPostId);
    }

    public function testInvalidRecordTypesAreRejectedBeforeStorageChanges(): void
    {
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [20]);

        try {
            $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [30]);
            self::fail('An invalid target type should fail.');
        } catch (InvalidArgumentException) {
            self::assertSame(
                [20],
                $this->service->relatedPostIds(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE),
            );
        }
    }

    public function testDeletingARecordRemovesOnlyItsAssociationRows(): void
    {
        $this->service->replace(10, RelationType::ACTIVITY_AVAILABLE_AT_PLACE, [20]);
        $this->service->replace(40, RelationType::PACKAGE_COVERS_PLACE, [21]);

        self::assertSame(1, $this->repository->deleteForPost(10));
        self::assertSame([], $this->service->relatedPostIds(20, RelationType::ACTIVITY_AVAILABLE_AT_PLACE));
        self::assertSame([21], $this->service->relatedPostIds(40, RelationType::PACKAGE_COVERS_PLACE));
    }
}
