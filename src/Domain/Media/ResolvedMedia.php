<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

use InvalidArgumentException;

final readonly class ResolvedMedia
{
    public function __construct(
        public ?int $attachmentId,
        public ?string $url,
        public string $source,
    ) {
        $hasAttachment = $this->attachmentId !== null && $this->attachmentId > 0;
        $hasUrl = $this->url !== null && $this->url !== '';

        if ($hasAttachment === $hasUrl) {
            throw new InvalidArgumentException('Resolved media must contain exactly one usable source.');
        }
    }
}
