<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Permalink;

final readonly class PermalinkValidationResult
{
    /**
     * @param array<string, string> $bases
     * @param list<string>          $errors
     */
    public function __construct(public array $bases, public array $errors) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
