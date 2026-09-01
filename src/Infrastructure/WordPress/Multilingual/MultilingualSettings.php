<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final class MultilingualSettings
{
    public const OPTION_FALLBACK = 'ads_tourism_multilingual_fallback_to_original';

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_FALLBACK, [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ]);
        add_settings_section(
            'ads_tourism_multilingual_section',
            __('Multilingual integration', 'ads-tourism'),
            [$this, 'renderDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_FALLBACK,
            __('Missing translations', 'ads-tourism'),
            [$this, 'renderFallbackField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_multilingual_section',
        );
    }

    public function fallbackToOriginal(): bool
    {
        return (bool) get_option(self::OPTION_FALLBACK, true);
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'ADS Tourism stores language-neutral relationships and can resolve translated records through WPML or Polylang when either plugin is active.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderFallbackField(): void
    {
        echo '<input type="hidden" name="' . esc_attr(self::OPTION_FALLBACK) . '" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_FALLBACK) . '" value="1" ';
        echo checked($this->fallbackToOriginal(), true, false) . '> ';
        echo esc_html__('Use the original-language record when no translated equivalent exists.', 'ads-tourism');
        echo '</label>';
    }
}
