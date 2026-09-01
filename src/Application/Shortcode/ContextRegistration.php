<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Shortcode;

final readonly class ContextRegistration
{
    public function __construct(
        public bool $accepted,
        public string $message = '',
    ) {}
}
