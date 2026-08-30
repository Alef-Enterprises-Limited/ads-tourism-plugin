<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class PermalinkRedirector
{
    public function __construct(private PermalinkSettings $settings) {}

    public function redirectOldUrl(): void
    {
        if (!is_404()) {
            return;
        }

        $requestUri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field((string) wp_unslash($_SERVER['REQUEST_URI']))
            : '';
        $parsedPath = wp_parse_url($requestUri, PHP_URL_PATH);

        if (!is_string($parsedPath)) {
            return;
        }

        $path = $parsedPath;
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if ($segments === []) {
            return;
        }

        $redirects = get_option(PermalinkSettings::OPTION_REDIRECTS, []);
        $redirects = is_array($redirects) ? $redirects : [];
        $requestedBase = $segments[0];

        if (isset($redirects[$requestedBase]) && is_string($redirects[$requestedBase])) {
            $segments[0] = $redirects[$requestedBase];
            $this->redirect(home_url('/' . implode('/', $segments) . '/'));
        }

        $contentType = $this->contentTypeForBase($requestedBase);

        if ($contentType === null || count($segments) < 2) {
            return;
        }

        $oldSlug = end($segments);
        $matches = get_posts([
            'post_type' => $contentType->value,
            'post_status' => 'publish',
            'fields' => 'ids',
            'numberposts' => 1,
            'meta_key' => SlugHistory::META_KEY,
            'meta_value' => $oldSlug,
        ]);

        if ($matches === []) {
            return;
        }

        $matchedPost = $matches[0];
        $matchedPostId = $matchedPost instanceof \WP_Post ? $matchedPost->ID : $matchedPost;
        $permalink = get_permalink($matchedPostId);

        if (is_string($permalink) && $permalink !== '') {
            $this->redirect($permalink);
        }
    }

    private function contentTypeForBase(string $base): ?ContentType
    {
        foreach (ContentType::cases() as $contentType) {
            if ($this->settings->contentTypeBase($contentType) === $base) {
                return $contentType;
            }
        }

        return null;
    }

    private function redirect(string $url): never
    {
        wp_safe_redirect($url, 301, 'ADS Tourism');
        exit;
    }
}
