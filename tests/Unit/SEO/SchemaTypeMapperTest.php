<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\SEO;

use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaTypeMapper;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SchemaTypeMapperTest extends TestCase
{
    /** @return iterable<string, array{ContentType, list<string>}> */
    public static function mappings(): iterable
    {
        yield 'place' => [ContentType::PLACE, ['TouristAttraction', 'Place']];
        yield 'activity' => [ContentType::ACTIVITY, ['TouristAttraction']];
        yield 'stay' => [ContentType::STAY, ['LodgingBusiness']];
        yield 'operator' => [ContentType::OPERATOR, ['TravelAgency', 'Organization']];
        yield 'package' => [ContentType::PACKAGE, ['TouristTrip']];
    }

    /** @param list<string> $expected */
    #[DataProvider('mappings')]
    public function testItMapsTourismRecordsToRelevantSchemaTypes(ContentType $type, array $expected): void
    {
        self::assertSame($expected, (new SchemaTypeMapper())->for($type));
    }
}
