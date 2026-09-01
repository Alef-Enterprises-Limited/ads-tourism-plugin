<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use WP_Post;

final readonly class SeoDataResolver
{
    public function __construct(
        private RelationshipRepository $relationships,
        private TranslationResolver $translations,
    ) {}

    /** @return array<string, mixed> */
    public function forPost(int $postId): array
    {
        $post = get_post($postId);

        if (!$post instanceof WP_Post || ContentType::tryFrom($post->post_type) === null) {
            return [];
        }

        $canonical = get_permalink($post);
        $summary = apply_filters('ads_tourism_resolved_field', null, $post->ID, 'ads_tourism_summary');
        $description = is_string($summary) && trim($summary) !== ''
            ? wp_trim_words(wp_strip_all_tags($summary), 40)
            : wp_trim_words(wp_strip_all_tags($post->post_excerpt), 40);
        $resolvedMedia = apply_filters('ads_tourism_featured_media', null, $post->ID);
        $image = $this->mediaUrl($resolvedMedia);
        $data = [
            'post_id' => $post->ID,
            'post_type' => $post->post_type,
            'title' => get_the_title($post),
            'description' => $description,
            'canonical' => is_string($canonical) ? $canonical : '',
            'image' => $image,
            'coordinates' => $this->coordinates($post->ID, $post->post_type),
            'price' => $this->price($post->ID),
            'taxonomies' => $this->taxonomies($post->ID, $post->post_type),
            'relationships' => $this->relationshipIds($post->ID, $post->post_type),
        ];
        $filtered = apply_filters('ads_tourism_seo_data', $data, $post->ID);

        return is_array($filtered) ? $filtered : $data;
    }

    private function mediaUrl(mixed $media): string
    {
        if (!$media instanceof ResolvedMedia) {
            return '';
        }

        if ($media->url !== null) {
            return $media->url;
        }

        $url = wp_get_attachment_image_url((int) $media->attachmentId, 'full');

        return is_string($url) ? $url : '';
    }

    /** @return array{latitude: float, longitude: float}|array{} */
    private function coordinates(int $postId, string $postType): array
    {
        $prefix = $postType === ContentType::PACKAGE->value ? 'meeting_point_' : '';
        $latitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'latitude', true);
        $longitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'longitude', true);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return [];
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return [];
        }

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    /** @return array{amount: float, currency: string}|array{} */
    private function price(int $postId): array
    {
        $amount = get_post_meta($postId, 'ads_tourism_price_from', true);
        $currency = strtoupper(sanitize_key((string) get_post_meta(
            $postId,
            'ads_tourism_price_currency',
            true,
        )));

        if (!is_numeric($amount) || preg_match('/^[A-Z]{3}$/', $currency) !== 1) {
            return [];
        }

        return ['amount' => (float) $amount, 'currency' => $currency];
    }

    /** @return array<string, list<string>> */
    private function taxonomies(int $postId, string $postType): array
    {
        $values = [];

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            if (!in_array($postType, $taxonomy->objectTypes(), true)) {
                continue;
            }

            $slugs = wp_get_object_terms($postId, $taxonomy->value, ['fields' => 'slugs']);

            if (is_array($slugs) && $slugs !== []) {
                $values[$taxonomy->value] = array_values(array_filter($slugs, 'is_string'));
            }
        }

        return $values;
    }

    /** @return array<string, list<int>> */
    private function relationshipIds(int $postId, string $postType): array
    {
        $contentType = ContentType::tryFrom($postType);

        if ($contentType === null) {
            return [];
        }

        $values = [];

        foreach (RelationType::forContentType($contentType) as $relationType) {
            $side = $relationType->sideFor($contentType);

            if ($side === null) {
                continue;
            }

            foreach ($this->relationships->findForRecord($postId, $relationType, $side) as $relationship) {
                $relatedId = $relationship->relatedPostId($side);
                $relatedType = (string) get_post_type($relatedId);
                $translatedId = $this->translations->postId($relatedId, $relatedType);

                if ($translatedId !== null) {
                    $values[$relationType->value][] = $translatedId;
                }
            }
        }

        foreach ($values as $relation => $ids) {
            $values[$relation] = array_values(array_unique($ids));
        }

        return $values;
    }
}
