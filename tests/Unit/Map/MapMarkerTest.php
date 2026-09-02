<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Map;

use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;
use PHPUnit\Framework\TestCase;

final class MapMarkerTest extends TestCase
{
    public function testItProducesAStablePublicPayload(): void
    {
        $marker = new MapMarker(
            42,
            new Coordinates(-4.341, 152.268),
            'Kokopo',
            'https://example.com/places/kokopo/',
            'ads_place',
            'A coastal destination.',
        );

        self::assertSame([
            'id' => 42,
            'latitude' => -4.341,
            'longitude' => 152.268,
            'title' => 'Kokopo',
            'url' => 'https://example.com/places/kokopo/',
            'content_type' => 'ads_place',
            'summary' => 'A coastal destination.',
            'location_label' => '',
            'location_role' => 'primary',
        ], $marker->toArray());
    }
}
