<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Presentation\LayoutMode;

final class DiviCompatibility
{
    public function register(): void
    {
        add_filter('et_builder_post_types', [$this, 'addPostTypes']);
        add_filter('et_builder_third_party_post_types', [$this, 'addPostTypes']);
        add_filter('et_builder_enabled_post_types', [$this, 'addPostTypes']);
        add_filter('body_class', [$this, 'addBodyClasses']);
    }

    /**
     * @param mixed $postTypes
     *
     * @return list<string>
     */
    public function addPostTypes(mixed $postTypes): array
    {
        $enabled = is_array($postTypes) ? array_values(array_filter($postTypes, 'is_string')) : [];

        foreach (ContentType::cases() as $contentType) {
            $enabled[] = $contentType->value;
        }

        return array_values(array_unique($enabled));
    }

    /**
     * @param mixed $classes
     *
     * @return list<string>
     */
    public function addBodyClasses(mixed $classes): array
    {
        $bodyClasses = is_array($classes) ? array_values(array_filter($classes, 'is_string')) : [];
        $contentType = ContentType::tryFrom((string) get_post_type());

        if ($contentType !== null) {
            $bodyClasses[] = 'ads-tourism-record';
            $bodyClasses[] = 'ads-tourism-record--' . str_replace('_', '-', $contentType->value);
            $layout = LayoutMode::fromStoredValue(get_post_meta(get_the_ID(), 'ads_tourism_layout_mode', true));
            $bodyClasses[] = 'ads-tourism-layout--' . str_replace('_', '-', $layout->value);
        }

        return array_values(array_unique($bodyClasses));
    }

    public function isActive(): bool
    {
        if (defined('ET_BUILDER_VERSION') || function_exists('et_pb_is_pagebuilder_used')) {
            return true;
        }

        $theme = wp_get_theme();
        $names = [strtolower((string) $theme->get('Name')), strtolower((string) $theme->get('Template'))];

        return in_array('divi', $names, true) || in_array('extra', $names, true);
    }

    /** @return array<string, bool> */
    public function statusByPostType(): array
    {
        $enabled = $this->addPostTypes($this->diviEnabledPostTypes());
        $status = [];

        foreach (ContentType::cases() as $contentType) {
            $status[$contentType->value] = in_array($contentType->value, $enabled, true);
        }

        return $status;
    }

    /** @return list<string> */
    private function diviEnabledPostTypes(): array
    {
        if (function_exists('et_builder_get_enabled_builder_post_types')) {
            $postTypes = et_builder_get_enabled_builder_post_types();

            return is_array($postTypes) ? array_values(array_filter($postTypes, 'is_string')) : [];
        }

        $postTypes = get_option('et_pb_post_type_integration', []);

        return is_array($postTypes) ? array_values(array_filter($postTypes, 'is_string')) : [];
    }
}
