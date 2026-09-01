<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Integration;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi\DiviCompatibility;
use PHPUnit\Framework\TestCase;

final class DiviCompatibilityTest extends TestCase
{
    public function testItAddsEveryTourismPostTypeWithoutRemovingExistingTypes(): void
    {
        $postTypes = (new DiviCompatibility())->addPostTypes(['post', 'page', 'ads_place']);

        self::assertContains('post', $postTypes);
        self::assertContains('page', $postTypes);

        foreach (ContentType::cases() as $contentType) {
            self::assertContains($contentType->value, $postTypes);
            self::assertSame(1, array_count_values($postTypes)[$contentType->value]);
        }
    }

    public function testItHandlesUnexpectedFilterValues(): void
    {
        $postTypes = (new DiviCompatibility())->addPostTypes(null);

        self::assertCount(count(ContentType::cases()), $postTypes);
    }
}
