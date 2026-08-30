<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaAttachmentResolver;

final class WordPressMediaAttachmentResolver implements MediaAttachmentResolver
{
    public function exists(int $attachmentId): bool
    {
        return get_post_type($attachmentId) === 'attachment';
    }
}
