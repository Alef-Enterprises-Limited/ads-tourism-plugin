<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\SEO;

use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaGraphMerger;
use PHPUnit\Framework\TestCase;

final class SchemaGraphMergerTest extends TestCase
{
    private SchemaGraphMerger $merger;

    protected function setUp(): void
    {
        $this->merger = new SchemaGraphMerger();
    }

    public function testItAddsANewTourismEntityWithoutItsStandaloneContext(): void
    {
        $candidate = [
            '@context' => 'https://schema.org',
            '@type' => ['TouristAttraction', 'Place'],
            '@id' => 'https://example.com/kokopo/#tourism-record',
            'url' => 'https://example.com/kokopo/',
        ];

        $graph = $this->merger->appendWithoutDuplicate([], $candidate);

        self::assertCount(1, $graph);
        self::assertArrayNotHasKey('@context', $graph[0]);
    }

    public function testItDoesNotDuplicateAnExistingSchemaIdentifier(): void
    {
        $existing = [[
            '@type' => 'Place',
            '@id' => 'https://example.com/kokopo/#tourism-record',
        ]];
        $candidate = [
            '@type' => 'TouristAttraction',
            '@id' => 'https://example.com/kokopo/#tourism-record',
        ];

        self::assertSame($existing, $this->merger->appendWithoutDuplicate($existing, $candidate));
    }

    public function testItDoesNotDuplicateTheSameUrlAndSchemaType(): void
    {
        $existing = [[
            '@type' => ['Place', 'Organization'],
            '@id' => 'https://example.com/schema/place',
            'url' => 'https://example.com/kokopo/',
        ]];
        $candidate = [
            '@type' => ['TouristAttraction', 'Place'],
            '@id' => 'https://example.com/kokopo/#tourism-record',
            'url' => 'https://example.com/kokopo/',
        ];

        self::assertSame($existing, $this->merger->appendWithoutDuplicate($existing, $candidate));
    }
}
