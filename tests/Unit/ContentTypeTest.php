<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use PHPUnit\Framework\TestCase;

final class ContentTypeTest extends TestCase
{
    public function testItDefinesTheFivePublicTourismRecords(): void
    {
        self::assertSame(
            ['ads_place', 'ads_activity', 'ads_stay', 'ads_operator', 'ads_package'],
            array_map(
                static fn (ContentType $contentType): string => $contentType->value,
                ContentType::cases(),
            ),
        );
    }

    public function testOnlyPlacesAreHierarchical(): void
    {
        self::assertTrue(ContentType::PLACE->isHierarchical());
        self::assertFalse(ContentType::ACTIVITY->isHierarchical());
        self::assertFalse(ContentType::STAY->isHierarchical());
        self::assertFalse(ContentType::OPERATOR->isHierarchical());
        self::assertFalse(ContentType::PACKAGE->isHierarchical());
    }

    public function testEveryRecordSupportsTheVisualContentPrimitives(): void
    {
        foreach (ContentType::cases() as $contentType) {
            self::assertContains('editor', $contentType->supportedEditorFeatures());
            self::assertContains('thumbnail', $contentType->supportedEditorFeatures());
            self::assertContains('revisions', $contentType->supportedEditorFeatures());
            self::assertContains('custom-fields', $contentType->supportedEditorFeatures());
        }
    }

    public function testRewriteBasesAreUnique(): void
    {
        $rewriteBases = array_map(
            static fn (ContentType $contentType): string => $contentType->rewriteBase(),
            ContentType::cases(),
        );

        self::assertSame($rewriteBases, array_values(array_unique($rewriteBases)));
    }
}
