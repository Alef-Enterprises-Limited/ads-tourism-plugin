<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Fallback;

use AlefDigitalSolutions\ADSTourism\Application\Fallback\FallbackResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class RecordFieldFallbackResolver
{
    public function __construct(private FallbackResolver $fallbacks) {}

    public function resolve(int $postId, string $metaKey): mixed
    {
        $contentType = ContentType::tryFrom((string) get_post_type($postId));

        if ($contentType === null) {
            return null;
        }

        $overrides = get_post_meta($postId, 'ads_tourism_display_fallback_overrides', true);
        $contentTypeDefaults = get_option('ads_tourism_content_type_field_defaults', []);
        $globalDefaults = get_option('ads_tourism_global_field_defaults', []);
        $contentTypeValues = is_array($contentTypeDefaults)
            && isset($contentTypeDefaults[$contentType->value])
            && is_array($contentTypeDefaults[$contentType->value])
                ? $contentTypeDefaults[$contentType->value]
                : [];

        return $this->fallbacks->resolve(
            get_post_meta($postId, $metaKey, true),
            is_array($overrides) ? ($overrides[$metaKey] ?? null) : null,
            $contentTypeValues[$metaKey] ?? null,
            is_array($globalDefaults) ? ($globalDefaults[$metaKey] ?? null) : null,
        );
    }
}
