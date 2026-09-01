<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

interface MediaAttachmentResolver
{
    public function exists(int $attachmentId): bool;
}
