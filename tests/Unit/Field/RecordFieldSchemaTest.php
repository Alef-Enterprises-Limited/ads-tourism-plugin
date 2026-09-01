<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Field;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use PHPUnit\Framework\TestCase;

final class RecordFieldSchemaTest extends TestCase
{
    private RecordFieldSchema $schema;

    protected function setUp(): void
    {
        $this->schema = new RecordFieldSchema();
    }

    public function testEveryRecordHasTheCommonWorkflowAndDisplayFields(): void
    {
        foreach (ContentType::cases() as $contentType) {
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_external_id'));
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_layout_mode'));
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_verification_status'));
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_external_featured_media_url'));
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_gallery_max_images'));
            self::assertNotNull($this->schema->find($contentType, 'ads_tourism_gallery_pagination'));
        }
    }

    public function testFieldKeysAreUniqueWithinEveryRecordType(): void
    {
        foreach (ContentType::cases() as $contentType) {
            $keys = array_map(
                static fn(FieldDefinition $field): string => $field->key,
                $this->schema->for($contentType),
            );

            self::assertSame($keys, array_values(array_unique($keys)), $contentType->value);
        }
    }

    public function testPackagesHaveStructuredItineraryAndCommerceFields(): void
    {
        $itinerary = $this->schema->find(ContentType::PACKAGE, 'ads_tourism_itinerary');
        $commerce = $this->schema->find(ContentType::PACKAGE, 'ads_tourism_commerce_mode');

        self::assertSame(FieldType::ARRAY, $itinerary?->type);
        self::assertSame([], $itinerary?->default);
        self::assertSame('catalogue', $commerce?->default);
        self::assertArrayHasKey('woocommerce', $commerce?->options ?? []);
    }

    public function testCoordinatesAreTypedAsNumbers(): void
    {
        foreach ([ContentType::PLACE, ContentType::STAY, ContentType::OPERATOR] as $contentType) {
            self::assertSame(
                FieldType::NUMBER,
                $this->schema->find($contentType, 'ads_tourism_latitude')?->type,
            );
            self::assertSame(
                FieldType::NUMBER,
                $this->schema->find($contentType, 'ads_tourism_longitude')?->type,
            );
        }
    }
}
