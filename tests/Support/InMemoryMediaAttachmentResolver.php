<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaAttachmentResolver;

final readonly class InMemoryMediaAttachmentResolver implements MediaAttachmentResolver
{
    /** @param list<int> $attachmentIds */
    public function __construct(private array $attachmentIds) {}

    public function exists(int $attachmentId): bool
    {
        return in_array($attachmentId, $this->attachmentIds, true);
    }
}
