<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Application\Location\LocationService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use InvalidArgumentException;
use Throwable;
use WP_Post;

final readonly class MapMarkerFactory
{
    public function __construct(
        private TranslationResolver $translations,
        private LocationService $locations,
    ) {}

    public function forPost(int $postId): ?MapMarker
    {
        return $this->forPostLocations($postId)[0] ?? null;
    }

    /** @return list<MapMarker> */
    public function forPostLocations(int $postId, bool $allLocations = false): array
    {
        return $this->forPostLocationsWithoutTranslationCycle($postId, $allLocations, []);
    }

    /**
     * @param array<int, true> $visited
     * @return list<MapMarker>
     */
    private function forPostLocationsWithoutTranslationCycle(
        int $postId,
        bool $allLocations,
        array $visited,
    ): array {
        if (isset($visited[$postId])) {
            return [];
        }

        $visited[$postId] = true;
        $post = get_post($postId);

        if (!$post instanceof WP_Post || $post->post_status !== 'publish') {
            return [];
        }

        $contentType = ContentType::tryFrom($post->post_type);

        if ($contentType === null) {
            return [];
        }

        $translatedId = $this->translations->postId($post->ID, $post->post_type);

        if ($translatedId !== null && $translatedId !== $post->ID) {
            return $this->forPostLocationsWithoutTranslationCycle($translatedId, $allLocations, $visited);
        }

        if ($translatedId === null) {
            return [];
        }

        $url = get_permalink($post);

        if (!is_string($url)) {
            return [];
        }

        $summary = apply_filters('ads_tourism_resolved_field', null, $post->ID, 'ads_tourism_summary');
        $summary = is_string($summary) ? wp_trim_words(wp_strip_all_tags($summary), 24) : '';
        $locations = $this->locationsForPost($post->ID, $contentType, $allLocations);
        /** @var list<MapMarker> $markers */
        $markers = [];

        foreach ($locations as $location) {
            $markers[] = new MapMarker(
                $post->ID,
                $location->coordinates,
                get_the_title($post),
                $url,
                $contentType->value,
                $summary,
                $location->label,
                $location->role->value,
            );
        }

        return $markers;
    }

    /**
     * @param list<int> $postIds
     *
     * @return list<MapMarker>
     */
    public function forPosts(array $postIds, int $limit = 100, bool $allLocations = false): array
    {
        $markers = [];
        $limit = min(100, max(1, $limit));

        foreach (array_slice($postIds, 0, 100) as $postId) {
            foreach ($this->forPostLocations((int) $postId, $allLocations) as $marker) {
                if ($allLocations) {
                    $markers[] = $marker;
                } else {
                    $markers[$marker->postId] = $marker;
                }

                if (count($markers) >= $limit) {
                    return array_values($markers);
                }
            }
        }

        return array_values($markers);
    }

    /** @return list<LocationPoint> */
    private function locationsForPost(int $postId, ContentType $contentType, bool $allLocations): array
    {
        try {
            $locations = $this->locations->find($postId);

            if ($locations !== []) {
                if ($allLocations) {
                    return array_values(array_filter(
                        $locations,
                        static fn(LocationPoint $location): bool => $location->showOnMap,
                    ));
                }

                foreach ($locations as $location) {
                    if ($location->isPrimary && $location->showOnMap) {
                        return [$location];
                    }
                }

                foreach ($locations as $location) {
                    if ($location->showOnMap) {
                        return [$location];
                    }
                }

                return [];
            }
        } catch (Throwable) {
            // A legacy coordinate fallback keeps existing records usable if the table is unavailable.
        }

        return $this->legacyLocations($postId, $contentType);
    }

    /** @return list<LocationPoint> */
    private function legacyLocations(int $postId, ContentType $contentType): array
    {
        $prefix = $contentType === ContentType::PACKAGE ? 'meeting_point_' : '';
        $latitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'latitude', true);
        $longitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'longitude', true);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return [];
        }

        try {
            return [new LocationPoint(
                $postId,
                new Coordinates((float) $latitude, (float) $longitude),
                $prefix === '' ? __('Primary location', 'ads-tourism') : __('Meeting point', 'ads-tourism'),
                $prefix === '' ? LocationRole::PRIMARY : LocationRole::MEETING_POINT,
                true,
                true,
            )];
        } catch (InvalidArgumentException) {
            return [];
        }
    }
}
