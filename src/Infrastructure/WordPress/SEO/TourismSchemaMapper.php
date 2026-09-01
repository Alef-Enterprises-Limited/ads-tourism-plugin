<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO;

use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaTypeMapper;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class TourismSchemaMapper
{
    public function __construct(
        private SchemaTypeMapper $types,
        private SeoDataResolver $seo,
    ) {}

    /** @return array<string, mixed> */
    public function forPost(int $postId): array
    {
        $seo = $this->seo->forPost($postId);
        $contentType = ContentType::tryFrom((string) ($seo['post_type'] ?? ''));

        if ($seo === [] || $contentType === null || (string) ($seo['canonical'] ?? '') === '') {
            return [];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->types->for($contentType),
            '@id' => $seo['canonical'] . '#tourism-record',
            'name' => $seo['title'],
            'url' => $seo['canonical'],
        ];
        $this->addIfUsable($schema, 'description', $seo['description'] ?? '');
        $this->addIfUsable($schema, 'image', $seo['image'] ?? '');
        $this->addContactData($schema, $postId);
        $this->addLocationData($schema, $postId, $contentType);
        $this->addCommercialGuidance($schema, $postId, $contentType);
        $this->addRelationships($schema, $seo, $contentType);
        $schema = apply_filters('ads_tourism_schema_data', $schema, $postId, $contentType->value);

        return is_array($schema) ? $schema : [];
    }

    /** @param array<string, mixed> $schema */
    private function addContactData(array &$schema, int $postId): void
    {
        $this->addIfUsable($schema, 'telephone', get_post_meta($postId, 'ads_tourism_telephone', true));
        $this->addIfUsable($schema, 'email', get_post_meta($postId, 'ads_tourism_email', true));
        $this->addIfUsable($schema, 'sameAs', get_post_meta($postId, 'ads_tourism_website_url', true));
    }

    /** @param array<string, mixed> $schema */
    private function addLocationData(array &$schema, int $postId, ContentType $contentType): void
    {
        $prefix = $contentType === ContentType::PACKAGE ? 'meeting_point_' : '';
        $latitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'latitude', true);
        $longitude = get_post_meta($postId, 'ads_tourism_' . $prefix . 'longitude', true);

        if (is_numeric($latitude) && is_numeric($longitude)) {
            $latitude = (float) $latitude;
            $longitude = (float) $longitude;

            if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
                $schema['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            }
        }

        $addressKey = match ($contentType) {
            ContentType::PLACE => 'physical_address',
            ContentType::STAY => 'address',
            ContentType::OPERATOR => 'office_address',
            ContentType::PACKAGE => 'meeting_point',
            ContentType::ACTIVITY => '',
        };

        if ($addressKey !== '') {
            $this->addIfUsable($schema, 'address', get_post_meta($postId, 'ads_tourism_' . $addressKey, true));
        }
    }

    /** @param array<string, mixed> $schema */
    private function addCommercialGuidance(array &$schema, int $postId, ContentType $contentType): void
    {
        if ($contentType !== ContentType::STAY && $contentType !== ContentType::PACKAGE) {
            return;
        }

        $price = get_post_meta($postId, 'ads_tourism_price_from', true);
        $currency = (string) get_post_meta($postId, 'ads_tourism_price_currency', true);

        $currency = strtoupper(sanitize_key($currency));

        if (is_numeric($price) && preg_match('/^[A-Z]{3}$/', $currency) === 1) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => (float) $price,
                'priceCurrency' => $currency,
                'url' => $schema['url'],
            ];
        }
    }

    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $seo
     */
    private function addRelationships(array &$schema, array $seo, ContentType $contentType): void
    {
        $taxonomies = $seo['taxonomies'] ?? [];

        if (is_array($taxonomies)) {
            $keywords = [];

            foreach ($taxonomies as $slugs) {
                if (is_array($slugs)) {
                    $keywords = [...$keywords, ...array_filter($slugs, 'is_string')];
                }
            }

            if ($keywords !== []) {
                $schema['keywords'] = array_values(array_unique($keywords));
            }
        }

        if ($contentType !== ContentType::PACKAGE || !is_array($seo['relationships'] ?? null)) {
            return;
        }

        $relationships = $seo['relationships'];
        $providerIds = array_merge(
            is_array($relationships['package_offered_by'] ?? null)
                ? $relationships['package_offered_by']
                : [],
            is_array($relationships['package_partner_provider'] ?? null)
                ? $relationships['package_partner_provider']
                : [],
        );

        foreach ($providerIds as $providerId) {
            if (!is_int($providerId) || $providerId < 1) {
                continue;
            }

            $url = get_permalink($providerId);
            $providerType = ContentType::tryFrom((string) get_post_type($providerId));

            if (!is_string($url) || !in_array($providerType, [ContentType::OPERATOR, ContentType::STAY], true)) {
                continue;
            }

            $schema['provider'] = [
                '@type' => $providerType === ContentType::STAY ? 'LodgingBusiness' : 'TravelAgency',
                '@id' => $url . '#tourism-record',
                'name' => get_the_title($providerId),
                'url' => $url,
            ];

            return;
        }
    }

    /** @param array<string, mixed> $schema */
    private function addIfUsable(array &$schema, string $key, mixed $value): void
    {
        if (is_scalar($value) && trim((string) $value) !== '') {
            $schema[$key] = (string) $value;
        }
    }
}
