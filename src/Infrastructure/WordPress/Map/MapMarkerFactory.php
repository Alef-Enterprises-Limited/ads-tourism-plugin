<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use InvalidArgumentException;
use WP_Post;

final readonly class MapMarkerFactory
{
    public function __construct(private TranslationResolver $translations) {}

    public function forPost(int $postId): ?MapMarker
    {
        return $this->forPostWithoutTranslationCycle($postId, []);
    }

    /** @param array<int, true> $visited */
    private function forPostWithoutTranslationCycle(int $postId, array $visited): ?MapMarker
    {
        if (isset($visited[$postId])) {
            return null;
        }

        $visited[$postId] = true;
        $post = get_post($postId);

        if (!$post instanceof WP_Post || $post->post_status !== 'publish') {
            return null;
        }

        $contentType = ContentType::tryFrom($post->post_type);

        if ($contentType === null) {
            return null;
        }

        $translatedId = $this->translations->postId($post->ID, $post->post_type);

        if ($translatedId !== null && $translatedId !== $post->ID) {
            return $this->forPostWithoutTranslationCycle($translatedId, $visited);
        }

        if ($translatedId === null) {
            return null;
        }

        $coordinates = $this->coordinates($post->ID, $contentType);
        $url = get_permalink($post);

        if ($coordinates === null || !is_string($url)) {
            return null;
        }

        $summary = apply_filters('ads_tourism_resolved_field', null, $post->ID, 'ads_tourism_summary');
        $summary = is_string($summary) ? wp_trim_words(wp_strip_all_tags($summary), 24) : '';

        return new MapMarker(
            $post->ID,
            $coordinates,
            get_the_title($post),
            $url,
            $contentType->value,
            $summary,
        );
    }

    /**
     * @param list<int> $postIds
     *
     * @return list<MapMarker>
     */
    public function forPosts(array $postIds, int $limit = 100): array
    {
        $markers = [];

        foreach (array_slice($postIds, 0, min(100, max(1, $limit))) as $postId) {
            $marker = $this->forPost($postId);

            if ($marker !== null) {
                $markers[$marker->postId] = $marker;
            }
        }

        return array_values($markers);
    }

    private function coordinates(int $postId, ContentType $contentType): ?Coordinates
    {
        $prefix = $contentType === ContentType::PACKAGE ? 'meeting_point_' : '';
        $latitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'latitude', true);
        $longitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'longitude', true);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        try {
            return new Coordinates((float) $latitude, (float) $longitude);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
