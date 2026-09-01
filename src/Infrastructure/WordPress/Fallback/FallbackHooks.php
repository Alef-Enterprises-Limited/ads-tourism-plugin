<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Fallback;

use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\FeaturedMediaResolver;

final readonly class FallbackHooks
{
    public function __construct(
        private RecordFieldFallbackResolver $fields,
        private FeaturedMediaResolver $featuredMedia,
    ) {}

    public function register(): void
    {
        add_filter('ads_tourism_resolved_field', [$this, 'resolveField'], 10, 3);
        add_filter('ads_tourism_featured_media', [$this, 'resolveFeaturedMedia'], 10, 2);
    }

    public function resolveField(mixed $currentValue, int $postId, string $metaKey): mixed
    {
        return $this->fields->resolve($postId, $metaKey);
    }

    public function resolveFeaturedMedia(mixed $currentValue, int $postId): ?ResolvedMedia
    {
        return $this->featuredMedia->resolve($postId);
    }
}
