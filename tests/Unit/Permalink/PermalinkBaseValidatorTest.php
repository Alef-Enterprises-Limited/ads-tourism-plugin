<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Permalink;

use AlefDigitalSolutions\ADSTourism\Domain\Permalink\PermalinkBaseValidator;
use PHPUnit\Framework\TestCase;

final class PermalinkBaseValidatorTest extends TestCase
{
    public function testUniqueLowercaseBasesAreValid(): void
    {
        $result = (new PermalinkBaseValidator())->validate([
            'places' => 'places-to-go',
            'activities' => 'things-to-do',
        ]);

        self::assertTrue($result->isValid());
        self::assertSame([], $result->errors);
    }

    public function testDuplicatesReservedBasesAndMalformedBasesAreRejected(): void
    {
        $result = (new PermalinkBaseValidator())->validate([
            'places' => 'search',
            'activities' => 'same-base',
            'stays' => 'same-base',
            'packages' => 'Bad Base',
        ]);

        self::assertFalse($result->isValid());
        self::assertCount(3, $result->errors);
    }
}
