<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final readonly class PresentationSettings
{
    public const OPTION_CUSTOM_CSS = 'ads_tourism_custom_css';

    public const OPTION_LOAD_STYLES = 'ads_tourism_load_frontend_styles';

    public function __construct(private CustomCssSanitizer $cssSanitizer) {}

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_LOAD_STYLES, [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ]);
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_CUSTOM_CSS, [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitizeCss'],
            'default' => '',
        ]);
        add_settings_section(
            'ads_tourism_presentation_section',
            __('Frontend presentation', 'ads-tourism'),
            [$this, 'renderDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_LOAD_STYLES,
            __('Fallback styles', 'ads-tourism'),
            [$this, 'renderLoadStylesField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_presentation_section',
        );
        add_settings_field(
            self::OPTION_CUSTOM_CSS,
            __('Custom CSS', 'ads-tourism'),
            [$this, 'renderCustomCssField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_presentation_section',
        );
    }

    public function loadStyles(): bool
    {
        return (bool) get_option(self::OPTION_LOAD_STYLES, true);
    }

    public function customCss(): string
    {
        return $this->cssSanitizer->sanitize(get_option(self::OPTION_CUSTOM_CSS, ''));
    }

    public function sanitizeCss(mixed $value): string
    {
        return $this->cssSanitizer->sanitize($value);
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'The bundled styles are deliberately minimal. Disable them when your theme or builder supplies all presentation.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderLoadStylesField(): void
    {
        echo '<input type="hidden" name="' . esc_attr(self::OPTION_LOAD_STYLES) . '" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_LOAD_STYLES) . '" value="1" ';
        echo checked($this->loadStyles(), true, false) . '> ';
        echo esc_html__('Load the ADS Tourism fallback stylesheet on tourism pages.', 'ads-tourism');
        echo '</label>';
    }

    public function renderCustomCssField(): void
    {
        echo '<textarea class="large-text code" rows="10" name="' . esc_attr(self::OPTION_CUSTOM_CSS) . '">';
        echo esc_textarea($this->customCss());
        echo '</textarea>';
        echo '<p class="description">';
        echo esc_html__(
            'Optional administrator CSS, limited to 50 KB. Unsafe style and script constructs are removed.',
            'ads-tourism',
        );
        echo '</p>';
    }
}
