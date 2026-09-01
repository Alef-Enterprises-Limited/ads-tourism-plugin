<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use WP_Post;

final class SlugHistory
{
    public const META_KEY = '_ads_tourism_previous_slugs';

    public function record(int $postId, WP_Post $updatedPost, WP_Post $previousPost): void
    {
        if (
            ContentType::tryFrom($updatedPost->post_type) === null
            || $previousPost->post_name === ''
            || $previousPost->post_name === $updatedPost->post_name
        ) {
            return;
        }

        $history = get_post_meta($postId, self::META_KEY, true);
        $history = is_array($history) ? $history : [];
        $slugs = [];

        foreach ($history as $slug) {
            if (is_string($slug) && $slug !== '') {
                $slugs[] = sanitize_title($slug);
            }
        }

        $slugs[] = sanitize_title($previousPost->post_name);
        $slugs = array_slice(array_values(array_unique($slugs)), -20);
        update_post_meta($postId, self::META_KEY, $slugs);
    }
}
