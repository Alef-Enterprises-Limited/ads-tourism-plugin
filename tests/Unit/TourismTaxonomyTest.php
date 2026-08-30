<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use PHPUnit\Framework\TestCase;

final class TourismTaxonomyTest extends TestCase
{
    public function testItDefinesNineTaxonomies(): void
    {
        self::assertCount(9, TourismTaxonomy::cases());
    }

    public function testTourismTagsAreTheOnlyFlatTaxonomy(): void
    {
        foreach (TourismTaxonomy::cases() as $taxonomy) {
            self::assertSame(
                $taxonomy !== TourismTaxonomy::TOURISM_TAG,
                $taxonomy->isHierarchical(),
            );
        }
    }

    public function testEveryTaxonomyIsAttachedToAtLeastOneRecord(): void
    {
        foreach (TourismTaxonomy::cases() as $taxonomy) {
            self::assertNotEmpty($taxonomy->objectTypes());
        }
    }

    public function testSharedDiscoveryTaxonomiesCoverEveryRecord(): void
    {
        $allRecords = array_map(
            static fn (ContentType $contentType): string => $contentType->value,
            ContentType::cases(),
        );

        self::assertSame($allRecords, TourismTaxonomy::TRAVELLER->objectTypes());
        self::assertSame($allRecords, TourismTaxonomy::ACCESSIBILITY->objectTypes());
        self::assertSame($allRecords, TourismTaxonomy::TOURISM_TAG->objectTypes());
        self::assertSame($allRecords, TourismTaxonomy::GEOGRAPHIC_AREA->objectTypes());
    }
}
