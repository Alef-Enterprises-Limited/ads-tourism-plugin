<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final class SeoSettings
{
    public const OPTION_MODE = 'ads_tourism_schema_mode';

    public const OPTION_SOCIAL = 'ads_tourism_native_social_metadata';

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_MODE, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitizeMode'],
            'default' => 'auto',
        ]);
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_SOCIAL, [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ]);
        add_settings_section(
            'ads_tourism_seo_section',
            __('Search and social metadata', 'ads-tourism'),
            [$this, 'renderDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_MODE,
            __('Structured data ownership', 'ads-tourism'),
            [$this, 'renderModeField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_seo_section',
        );
        add_settings_field(
            self::OPTION_SOCIAL,
            __('Native social metadata', 'ads-tourism'),
            [$this, 'renderSocialField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_seo_section',
        );
    }

    public function mode(): string
    {
        return $this->sanitizeMode(get_option(self::OPTION_MODE, 'auto'));
    }

    public function nativeSocialEnabled(): bool
    {
        return (bool) get_option(self::OPTION_SOCIAL, true);
    }

    public function nativeSchemaEnabled(bool $seoPluginActive): bool
    {
        return match ($this->mode()) {
            'native' => true,
            'disabled' => false,
            default => !$seoPluginActive,
        };
    }

    public function sanitizeMode(mixed $value): string
    {
        $mode = sanitize_key((string) $value);

        return in_array($mode, ['auto', 'native', 'disabled'], true) ? $mode : 'auto';
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'ADS Tourism can emit focused tourism schema itself or contribute it through a compatible SEO plugin.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderModeField(): void
    {
        $options = [
            'auto' => __('Automatic — defer to a detected SEO plugin', 'ads-tourism'),
            'native' => __('Always use ADS Tourism schema', 'ads-tourism'),
            'disabled' => __('Disable ADS Tourism schema', 'ads-tourism'),
        ];
        echo '<select name="' . esc_attr(self::OPTION_MODE) . '">';

        foreach ($options as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ';
            echo selected($this->mode(), $value, false) . '>' . esc_html($label) . '</option>';
        }

        echo '</select>';
    }

    public function renderSocialField(): void
    {
        echo '<input type="hidden" name="' . esc_attr(self::OPTION_SOCIAL) . '" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_SOCIAL) . '" value="1" ';
        echo checked($this->nativeSocialEnabled(), true, false) . '> ';
        echo esc_html__('Emit Open Graph and Twitter card inputs when no supported SEO plugin is active.', 'ads-tourism');
        echo '</label>';
    }
}
