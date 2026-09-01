<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use WP_Post;
use WP_Query;

final readonly class RelationshipSearchController
{
    public const ACTION = 'ads_tourism_relationship_search';

    public const NONCE_ACTION = 'ads_tourism_relationship_search';

    public function __construct(private RecordTypeResolver $recordTypes) {}

    public function search(): void
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('You cannot search tourism records.', 'ads-tourism')], 403);
        }

        $postId = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;
        $relationKey = isset($_GET['relation_key'])
            ? sanitize_key((string) wp_unslash($_GET['relation_key']))
            : '';
        $searchTerm = isset($_GET['search'])
            ? sanitize_text_field((string) wp_unslash($_GET['search']))
            : '';
        $page = isset($_GET['page']) ? max(1, absint($_GET['page'])) : 1;
        $relationType = RelationType::tryFrom($relationKey);
        $recordType = $this->recordTypes->resolve($postId);

        if ($relationType === null || $recordType === null) {
            wp_send_json_error(['message' => __('Invalid relationship search.', 'ads-tourism')], 400);
        }

        $targetTypes = array_map(
            static fn(ContentType $contentType): string => $contentType->value,
            $relationType->counterpartTypes($recordType),
        );

        if ($targetTypes === []) {
            wp_send_json_success(['items' => [], 'has_more' => false]);
        }

        $query = new WP_Query([
            'post_type' => $targetTypes,
            'post_status' => ['publish', 'pending', 'draft', 'private'],
            's' => $searchTerm,
            'posts_per_page' => 20,
            'paged' => $page,
            'post__not_in' => [$postId],
            'orderby' => 'title',
            'order' => 'ASC',
            'no_found_rows' => false,
        ]);
        $visiblePosts = array_values(array_filter(
            $query->posts,
            static fn(WP_Post $post): bool => current_user_can('edit_post', $post->ID),
        ));
        $items = array_map(
            static fn(WP_Post $post): array => [
                'id' => $post->ID,
                'title' => get_the_title($post),
                'post_type' => $post->post_type,
                'status' => $post->post_status,
            ],
            $visiblePosts,
        );

        wp_send_json_success([
            'items' => $items,
            'has_more' => $page < (int) $query->max_num_pages,
        ]);
    }
}
