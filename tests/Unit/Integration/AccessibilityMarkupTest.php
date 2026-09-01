<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Integration;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\PaginationRenderer;
use PHPUnit\Framework\TestCase;

final class AccessibilityMarkupTest extends TestCase
{
    public function testPaginationHasANameAndExposesTheCurrentPage(): void
    {
        $query = new TourismQuery(new ContextName('discover'), [ContentType::PLACE], page: 2);
        $result = new QueryResult([1], 60, 5, 2, 12);
        $html = (new PaginationRenderer())->render($result, $query, 'https://example.com/discover/');

        self::assertStringContainsString('<nav', $html);
        self::assertStringContainsString('aria-label="Tourism results pages"', $html);
        self::assertStringContainsString('aria-current="page">2</span>', $html);
        self::assertStringContainsString('rel="prev"', $html);
        self::assertStringContainsString('rel="next"', $html);
    }
}
