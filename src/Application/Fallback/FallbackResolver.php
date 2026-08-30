<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Fallback;

final class FallbackResolver
{
    public function resolve(
        mixed $recordValue,
        mixed $recordOverride = null,
        mixed $contentTypeDefault = null,
        mixed $globalDefault = null,
    ): mixed {
        foreach ([$recordValue, $recordOverride, $contentTypeDefault, $globalDefault] as $candidate) {
            if ($this->hasValue($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function hasValue(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        return !is_string($value) || trim($value) !== '';
    }
}
