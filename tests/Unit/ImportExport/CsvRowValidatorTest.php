<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRow;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidator;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use PHPUnit\Framework\TestCase;

final class CsvRowValidatorTest extends TestCase
{
    public function testValidatesMappedTourismValues(): void
    {
        $validator = new CsvRowValidator(new CsvSchema(new RecordFieldSchema()));
        $result = $validator->validate(ContentType::PLACE, new CsvRow(2, [
            'ID' => 'place-001',
            'Name' => 'Kokopo',
            'Lat' => '-4.341',
            'URL' => 'https://example.com/kokopo',
            'Types' => 'town|coastal-town',
        ]), [
            'ID' => 'external_id',
            'Name' => 'title',
            'Lat' => 'ads_tourism_latitude',
            'URL' => 'ads_tourism_website_url',
            'Types' => 'taxonomy_ads_place_type',
        ]);

        self::assertTrue($result->isValid());
        self::assertSame('place-001', $result->values['external_id']);
    }

    public function testRejectsUnsafeOrOutOfRangeValues(): void
    {
        $validator = new CsvRowValidator(new CsvSchema(new RecordFieldSchema()));
        $result = $validator->validate(ContentType::PLACE, new CsvRow(3, [
            'ID' => '=formula',
            'Name' => '',
            'Lat' => '95',
            'URL' => 'http://insecure.example.com',
        ]), [
            'ID' => 'external_id',
            'Name' => 'title',
            'Lat' => 'ads_tourism_latitude',
            'URL' => 'ads_tourism_website_url',
        ]);

        self::assertFalse($result->isValid());
        self::assertGreaterThanOrEqual(4, count($result->errors));
    }
}
