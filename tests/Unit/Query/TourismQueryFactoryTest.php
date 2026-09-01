<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Query;

use AlefDigitalSolutions\ADSTourism\Application\Query\TourismQueryFactory;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\PaginationMode;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TourismQueryFactoryTest extends TestCase
{
    public function testItBuildsAnAllowlistedPublicQuery(): void
    {
        $query = (new TourismQueryFactory())->create([
            'context' => 'kokopo-packages',
            'type' => 'packages',
            'query' => '<strong>diving</strong>',
            'page' => '2',
            'per_page' => '18',
            'sort' => 'price_asc',
            'pagination' => 'load_more',
            'taxonomies' => [TourismTaxonomy::PACKAGE_TYPE->value => ['Adventure', 'adventure']],
            'relationships' => ['place' => '42'],
            'minimum_price' => '100.50',
            'maximum_price' => '900',
            'minimum_duration' => '2',
            'maximum_duration' => '7',
        ]);

        self::assertSame('kokopo-packages', $query->context->value);
        self::assertSame([ContentType::PACKAGE], $query->contentTypes);
        self::assertSame('diving', $query->keyword);
        self::assertSame(2, $query->page);
        self::assertSame(18, $query->perPage);
        self::assertSame(QuerySort::PRICE_ASC, $query->sort);
        self::assertSame(PaginationMode::LOAD_MORE, $query->pagination);
        self::assertSame(
            ['adventure'],
            $query->taxonomyFilters[TourismTaxonomy::PACKAGE_TYPE->value],
        );
        self::assertSame(['place' => 42], $query->relationshipFilters);
        self::assertSame(100.5, $query->minimumPrice);
        self::assertSame(900.0, $query->maximumPrice);
        self::assertSame(2, $query->minimumDuration);
        self::assertSame(7, $query->maximumDuration);
    }

    public function testAllIncludesEveryTourismContentType(): void
    {
        $query = (new TourismQueryFactory())->create(['context' => 'all-records', 'type' => 'all']);

        self::assertSame(ContentType::cases(), $query->contentTypes);
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function invalidInputProvider(): iterable
    {
        yield 'unknown content type' => [['context' => 'results', 'type' => 'event']];
        yield 'excessive page size' => [['context' => 'results', 'per_page' => 25]];
        yield 'unknown sorting' => [['context' => 'results', 'sort' => 'relevance']];
        yield 'unknown taxonomy' => [['context' => 'results', 'taxonomies' => ['category' => ['news']]]];
        yield 'unsafe term slug' => [[
            'context' => 'results',
            'taxonomies' => [TourismTaxonomy::TOURISM_TAG->value => ['unsafe value']],
        ]];
        yield 'unknown relationship' => [['context' => 'results', 'relationships' => ['event' => 4]]];
        yield 'non-positive relationship id' => [['context' => 'results', 'relationships' => ['place' => 0]]];
        yield 'reversed price range' => [[
            'context' => 'results',
            'minimum_price' => 100,
            'maximum_price' => 50,
        ]];
    }

    #[DataProvider('invalidInputProvider')]
    public function testItRejectsUnsupportedOrUnboundedInput(array $input): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new TourismQueryFactory())->create($input);
    }
}
