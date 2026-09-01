<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipSide;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use PHPUnit\Framework\TestCase;

final class RelationTypeTest extends TestCase
{
    public function testTheTwelveCanonicalRelationKeysAreDefined(): void
    {
        self::assertCount(12, RelationType::cases());
        self::assertSame(
            'activity_available_at_place',
            RelationType::ACTIVITY_AVAILABLE_AT_PLACE->value,
        );
        self::assertSame('package_offered_by', RelationType::PACKAGE_OFFERED_BY->value);
    }

    public function testRelationshipsExposeBothEditorSides(): void
    {
        $relation = RelationType::PACKAGE_INCLUDES_ACTIVITY;

        self::assertSame(RelationshipSide::SOURCE, $relation->sideFor(ContentType::PACKAGE));
        self::assertSame(RelationshipSide::TARGET, $relation->sideFor(ContentType::ACTIVITY));
        self::assertSame([ContentType::ACTIVITY], $relation->counterpartTypes(ContentType::PACKAGE));
        self::assertSame([ContentType::PACKAGE], $relation->counterpartTypes(ContentType::ACTIVITY));
        self::assertSame('Included activities', $relation->labelFor(ContentType::PACKAGE));
        self::assertSame(
            'Packages including this activity',
            $relation->labelFor(ContentType::ACTIVITY),
        );
    }

    public function testPackageProvidersMayBeOperatorsOrStays(): void
    {
        self::assertSame(
            [ContentType::OPERATOR, ContentType::STAY],
            RelationType::PACKAGE_OFFERED_BY->targetTypes(),
        );
        self::assertTrue(RelationType::PACKAGE_OFFERED_BY->allowsPrimary());
    }
}
