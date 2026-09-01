<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Permalink;

final class PermalinkBaseValidator
{
    private const RESERVED_BASES = [
        'author',
        'category',
        'comments',
        'feed',
        'page',
        'search',
        'tag',
        'wp-admin',
        'wp-json',
    ];

    /** @param array<string, string> $bases */
    public function validate(array $bases): PermalinkValidationResult
    {
        $errors = [];
        $seen = [];

        foreach ($bases as $key => $base) {
            if ($base === '' || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $base) !== 1) {
                $errors[] = sprintf('%s must contain lowercase letters, numbers, and single hyphens only.', $key);
                continue;
            }

            if (in_array($base, self::RESERVED_BASES, true)) {
                $errors[] = sprintf('%s uses the reserved base "%s".', $key, $base);
            }

            if (isset($seen[$base])) {
                $errors[] = sprintf('%s duplicates the base used by %s.', $key, $seen[$base]);
            }

            $seen[$base] = $key;
        }

        return new PermalinkValidationResult($bases, $errors);
    }
}
