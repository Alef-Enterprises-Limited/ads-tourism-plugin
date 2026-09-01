<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual;

use AlefDigitalSolutions\ADSTourism\Application\Multilingual\TranslationAdapterInterface;

final class WordPressTranslationAdapter implements TranslationAdapterInterface
{
    public function key(): string
    {
        if ($this->hasWpml()) {
            return 'wpml';
        }

        if ($this->hasPolylang()) {
            return 'polylang';
        }

        return 'none';
    }

    public function isAvailable(): bool
    {
        return $this->hasWpml() || $this->hasPolylang();
    }

    public function currentLanguage(): string
    {
        if ($this->hasWpml()) {
            $language = apply_filters('wpml_current_language', null);

            return is_string($language) ? $language : '';
        }

        if ($this->hasPolylang()) {
            $language = $this->callPolylang('pll_current_language', 'slug');

            return is_string($language) ? $language : '';
        }

        return '';
    }

    public function translatedPostId(int $postId, string $postType): ?int
    {
        if ($this->hasWpml()) {
            $translated = apply_filters('wpml_object_id', $postId, $postType, false, $this->currentLanguage());

            return is_int($translated) && $translated > 0 ? $translated : null;
        }

        if ($this->hasPolylang()) {
            $translated = $this->callPolylang('pll_get_post', $postId, $this->currentLanguage());

            return is_int($translated) && $translated > 0 ? $translated : null;
        }

        return $postId;
    }

    public function translatedTermId(int $termId, string $taxonomy): ?int
    {
        if ($this->hasWpml()) {
            $translated = apply_filters('wpml_object_id', $termId, $taxonomy, false, $this->currentLanguage());

            return is_int($translated) && $translated > 0 ? $translated : null;
        }

        if ($this->hasPolylang()) {
            $translated = $this->callPolylang('pll_get_term', $termId, $this->currentLanguage());

            return is_int($translated) && $translated > 0 ? $translated : null;
        }

        return $termId;
    }

    private function hasWpml(): bool
    {
        return defined('ICL_SITEPRESS_VERSION') || has_filter('wpml_object_id') !== false;
    }

    private function hasPolylang(): bool
    {
        return function_exists('pll_get_post')
            && function_exists('pll_get_term')
            && function_exists('pll_current_language');
    }

    private function callPolylang(string $function, mixed ...$arguments): mixed
    {
        if (!is_callable($function)) {
            return null;
        }

        return $function(...$arguments);
    }
}
