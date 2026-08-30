<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use PHPUnit\Framework\TestCase;

final class CsvSchemaTest extends TestCase
{
    public function testEveryTemplateHasStableKeysAndRelevantTaxonomies(): void
    {
        $schema = new CsvSchema(new RecordFieldSchema());

        foreach (ContentType::cases() as $contentType) {
            $headers = $schema->headers($contentType);

            self::assertSame('external_id', $headers[0]);
            self::assertContains('title', $headers);
            self::assertContains('slug', $headers);
            self::assertContains('ads_tourism_summary', $headers);
            self::assertContains('taxonomy_ads_tourism_tag', $headers);
            self::assertSame($headers, array_values(array_unique($headers)));
        }
    }

    public function testHeaderNormalizationHandlesBomAndSpreadsheetLabels(): void
    {
        $schema = new CsvSchema(new RecordFieldSchema());

        self::assertSame('external_id', $schema->normalizeHeader("\xEF\xBB\xBFExternal ID"));
        self::assertSame('featured_media_url', $schema->normalizeHeader('Featured Media URL'));
    }
}
