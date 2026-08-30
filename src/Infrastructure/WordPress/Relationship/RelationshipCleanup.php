<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;

final readonly class RelationshipCleanup
{
    public function __construct(private RelationshipRepository $relationships) {}

    public function deleteForPost(int $postId): void
    {
        if (ContentType::tryFrom((string) get_post_type($postId)) === null) {
            return;
        }

        $this->relationships->deleteForPost($postId);
    }
}
