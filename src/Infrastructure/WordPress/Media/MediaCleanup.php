<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;

final readonly class MediaCleanup
{
    public function __construct(private MediaLinkRepository $mediaLinks) {}

    public function deleteForPost(int $postId): void
    {
        $postType = (string) get_post_type($postId);

        if (ContentType::tryFrom($postType) !== null) {
            $this->mediaLinks->deleteForEntity($postId);
        } elseif ($postType === 'attachment') {
            $this->mediaLinks->deleteForAttachment($postId);
        }
    }
}
