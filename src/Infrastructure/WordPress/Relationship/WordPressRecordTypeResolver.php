<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;

final class WordPressRecordTypeResolver implements RecordTypeResolver
{
    public function resolve(int $postId): ?ContentType
    {
        return ContentType::tryFrom((string) get_post_type($postId));
    }
}
