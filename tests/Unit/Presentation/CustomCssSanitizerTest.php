<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use PHPUnit\Framework\TestCase;

final class CustomCssSanitizerTest extends TestCase
{
    public function testItKeepsOrdinaryScopedCss(): void
    {
        $sanitizer = new CustomCssSanitizer();
        $css = '.ads-tourism-card { border-radius: 1rem; }';

        self::assertSame($css, $sanitizer->sanitize($css));
    }

    public function testItRemovesUnsafeStylesheetConstructs(): void
    {
        $sanitizer = new CustomCssSanitizer();
        $result = $sanitizer->sanitize(
            '</style><script>alert(1)</script>@import "https://example.com/x.css";a{behavior:url(x)}',
        );

        self::assertStringNotContainsStringIgnoringCase('style', $result);
        self::assertStringNotContainsStringIgnoringCase('script', $result);
        self::assertStringNotContainsStringIgnoringCase('@import', $result);
        self::assertStringNotContainsStringIgnoringCase('behavior:', $result);
    }

    public function testItBoundsStoredCss(): void
    {
        $sanitizer = new CustomCssSanitizer();

        self::assertSame(
            CustomCssSanitizer::MAX_LENGTH,
            strlen($sanitizer->sanitize(str_repeat('a', CustomCssSanitizer::MAX_LENGTH + 100))),
        );
    }

    public function testItRejectsNonStringValues(): void
    {
        self::assertSame('', (new CustomCssSanitizer())->sanitize(['not css']));
    }
}
