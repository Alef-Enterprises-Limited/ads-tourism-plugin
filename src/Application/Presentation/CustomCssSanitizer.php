<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Presentation;

final class CustomCssSanitizer
{
    public const MAX_LENGTH = 50_000;

    /** @var list<string> */
    private const UNSAFE_PATTERNS = [
        '/<\/?style\b/i',
        '/<\/?script\b/i',
        '/expression\s*\(/i',
        '/javascript\s*:/i',
        '/@import\b/i',
        '/behavior\s*:/i',
        '/-moz-binding\s*:/i',
    ];

    public function sanitize(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $css = str_replace("\0", '', trim($value));
        $css = strip_tags($css);

        foreach (self::UNSAFE_PATTERNS as $pattern) {
            $css = (string) preg_replace($pattern, '', $css);
        }

        return substr($css, 0, self::MAX_LENGTH);
    }
}
