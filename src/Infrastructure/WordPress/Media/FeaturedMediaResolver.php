<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Application\Fallback\MediaFallbackResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;

final readonly class FeaturedMediaResolver
{
    public function __construct(
        private MediaFallbackResolver $fallbacks,
        private MediaSettings $settings,
    ) {}

    public function resolve(int $postId): ?ResolvedMedia
    {
        $contentType = ContentType::tryFrom((string) get_post_type($postId));

        if ($contentType === null) {
            return null;
        }

        $settings = $this->settings->get();

        return $this->fallbacks->resolve([
            $this->attachment((int) get_post_thumbnail_id($postId), 'record_featured_image'),
            $this->external($postId),
            $this->attachment((int) $settings[$contentType->value], 'content_type_default'),
            $this->attachment((int) $settings['global'], 'global_default'),
        ]);
    }

    private function attachment(int $attachmentId, string $source): ?ResolvedMedia
    {
        return $attachmentId > 0 && get_post_type($attachmentId) === 'attachment'
            ? new ResolvedMedia($attachmentId, null, $source)
            : null;
    }

    private function external(int $postId): ?ResolvedMedia
    {
        $url = (string) get_post_meta($postId, 'ads_tourism_external_featured_media_url', true);
        $urlType = MediaUrlType::tryFrom(
            (string) get_post_meta($postId, 'ads_tourism_external_featured_media_url_type', true),
        );

        if ($url === '' || $urlType === null) {
            return null;
        }

        $resolvedUrl = $urlType === MediaUrlType::RELATIVE ? home_url($url) : $url;

        if (wp_http_validate_url($resolvedUrl) === false) {
            return null;
        }

        return new ResolvedMedia(null, $resolvedUrl, 'record_external_image');
    }
}
