<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TourismQueryTest extends TestCase
{
    public function testNormalizedStateOrdersFilterKeysForStableCaching(): void
    {
        $query = new TourismQuery(
            new ContextName('discover'),
            [ContentType::PLACE],
            taxonomyFilters: ['z_taxonomy' => ['z'], 'a_taxonomy' => ['a']],
            relationshipFilters: ['stay' => 20, 'place' => 10],
        );

        $state = $query->normalizedState();

        self::assertSame(['a_taxonomy', 'z_taxonomy'], array_keys($state['taxonomies']));
        self::assertSame(['place', 'stay'], array_keys($state['relationships']));
    }

    public function testItRejectsReversedDurationRanges(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TourismQuery(
            new ContextName('discover'),
            [ContentType::ACTIVITY],
            minimumDuration: 7,
            maximumDuration: 2,
        );
    }
}
