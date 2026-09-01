<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final class MapSettings
{
    public const OPTION_API_KEY = 'ads_tourism_google_maps_browser_key';

    public const OPTION_PROVIDER = 'ads_tourism_map_provider';

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_PROVIDER, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitizeProvider'],
            'default' => 'none',
        ]);
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_API_KEY, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitizeApiKey'],
            'default' => '',
        ]);
        add_settings_section(
            'ads_tourism_maps_section',
            __('Maps', 'ads-tourism'),
            [$this, 'renderDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_PROVIDER,
            __('Map provider', 'ads-tourism'),
            [$this, 'renderProviderField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_maps_section',
        );
        add_settings_field(
            self::OPTION_API_KEY,
            __('Google Maps browser key', 'ads-tourism'),
            [$this, 'renderApiKeyField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_maps_section',
        );
    }

    public function ensureOptions(): void
    {
        add_option(self::OPTION_PROVIDER, 'none', '', false);
        add_option(self::OPTION_API_KEY, '', '', false);
    }

    public function provider(): string
    {
        return $this->sanitizeProvider(get_option(self::OPTION_PROVIDER, 'none'));
    }

    public function googleApiKey(): string
    {
        $value = get_option(self::OPTION_API_KEY, '');

        return is_string($value) ? $value : '';
    }

    public function sanitizeProvider(mixed $value): string
    {
        $provider = sanitize_key((string) $value);
        $providers = apply_filters('ads_tourism_map_provider_labels', [
            'none' => __('Disabled', 'ads-tourism'),
            'google' => __('Google Maps', 'ads-tourism'),
        ]);

        return is_array($providers) && array_key_exists($provider, $providers) ? $provider : 'none';
    }

    public function sanitizeApiKey(mixed $value): string
    {
        if (isset($_POST['ads_tourism_clear_google_maps_key'])) {
            return '';
        }

        $key = trim((string) $value);

        if ($key === '') {
            return $this->googleApiKey();
        }

        if (strlen($key) > 200 || preg_match('/^[A-Za-z0-9_-]+$/', $key) !== 1) {
            add_settings_error(
                self::OPTION_API_KEY,
                'ads_tourism_invalid_google_maps_key',
                __('The Google Maps browser key contains unsupported characters.', 'ads-tourism'),
            );

            return $this->googleApiKey();
        }

        return $key;
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Maps are optional. Coordinates remain stored when maps are disabled or a provider is unavailable.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderProviderField(): void
    {
        $providers = apply_filters('ads_tourism_map_provider_labels', [
            'none' => __('Disabled', 'ads-tourism'),
            'google' => __('Google Maps', 'ads-tourism'),
        ]);
        $providers = is_array($providers) ? $providers : [];
        echo '<select name="' . esc_attr(self::OPTION_PROVIDER) . '">';

        foreach ($providers as $key => $label) {
            if (!is_string($key) || !is_scalar($label)) {
                continue;
            }

            echo '<option value="' . esc_attr($key) . '" ';
            echo selected($this->provider(), $key, false) . '>' . esc_html((string) $label) . '</option>';
        }

        echo '</select>';
    }

    public function renderApiKeyField(): void
    {
        $key = $this->googleApiKey();
        $mask = $key === '' ? __('Not configured', 'ads-tourism') : '••••' . substr($key, -4);
        echo '<input class="regular-text" type="password" autocomplete="new-password" name="';
        echo esc_attr(self::OPTION_API_KEY) . '" value="" placeholder="' . esc_attr($mask) . '">';
        echo '<p class="description">';
        echo esc_html__(
            'Use a browser-restricted key and restrict HTTP referrers to this website. The browser key is necessarily visible to visitors; never enter a server-only secret.',
            'ads-tourism',
        );
        echo '</p>';

        if ($key !== '') {
            echo '<label><input type="checkbox" name="ads_tourism_clear_google_maps_key" value="1"> ';
            echo esc_html__('Remove the stored Google Maps key', 'ads-tourism') . '</label>';
        }
    }
}
