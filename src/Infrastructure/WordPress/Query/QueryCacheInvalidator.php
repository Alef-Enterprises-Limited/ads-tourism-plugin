<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class QueryCacheInvalidator
{
    public function __construct(private PublicQueryCache $cache) {}

    public function register(): void
    {
        add_action('save_post', [$this, 'postChanged'], 100, 2);
        add_action('deleted_post', [$this, 'postDeleted'], 10, 2);
        add_action('created_term', [$this, 'termChanged']);
        add_action('edited_term', [$this, 'termChanged']);
        add_action('delete_term', [$this, 'termChanged']);
    }

    public function postChanged(int $postId, mixed $post): void
    {
        if (ContentType::tryFrom((string) get_post_type($postId)) !== null) {
            $this->cache->invalidate();
        }
    }

    public function postDeleted(int $postId, mixed $post): void
    {
        $postType = is_object($post) && isset($post->post_type) ? (string) $post->post_type : '';

        if (ContentType::tryFrom($postType) !== null) {
            $this->cache->invalidate();
        }
    }

    public function termChanged(): void
    {
        $this->cache->invalidate();
    }
}
