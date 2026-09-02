<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final readonly class PresentationSettings
{
    public const OPTION_CUSTOM_CSS = 'ads_tourism_custom_css';

    public const OPTION_LOAD_STYLES = 'ads_tourism_load_frontend_styles';

    public const OPTION_SCOPED_CSS = 'ads_tourism_scoped_css';

    public const ACTION_RESET_CSS = 'ads_tourism_reset_css';

    public const RESET_CONFIRMATION_FIELD = 'ads_tourism_reset_css_confirm';

    public const RESET_NONCE_FIELD = 'ads_tourism_reset_css_nonce';

    public function __construct(
        private CustomCssSanitizer $cssSanitizer,
        private BoilerplateStyles $boilerplate,
    ) {}

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
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_SCOPED_CSS, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeScopedCss'],
            'default' => [],
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
            __('Global CSS', 'ads-tourism'),
            [$this, 'renderGlobalCssField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_presentation_section',
        );
        add_settings_field(
            self::OPTION_SCOPED_CSS,
            __('Content type and widget CSS', 'ads-tourism'),
            [$this, 'renderScopedCssFields'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_presentation_section',
        );
        add_settings_field(
            self::ACTION_RESET_CSS,
            __('Reset CSS', 'ads-tourism'),
            [$this, 'renderResetField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_presentation_section',
        );
    }

    public function loadStyles(): bool
    {
        return (bool) get_option(self::OPTION_LOAD_STYLES, true);
    }

    /** Returns only administrator-saved global CSS for runtime output. */
    public function customCss(): string
    {
        $value = get_option(self::OPTION_CUSTOM_CSS, '');

        return is_string($value) ? $this->cssSanitizer->sanitize($value) : '';
    }

    /** @return array<string, string> */
    public function customCssByScope(): array
    {
        $css = [];
        $global = $this->customCss();

        if ($global !== '') {
            $css['global'] = $global;
        }

        $stored = get_option(self::OPTION_SCOPED_CSS, []);

        if (!is_array($stored)) {
            return $css;
        }

        foreach ($this->boilerplate->scopes() as $scope => $definition) {
            if ($scope === 'global' || !array_key_exists($scope, $stored) || !is_scalar($stored[$scope])) {
                continue;
            }

            $value = $this->cssSanitizer->sanitize((string) $stored[$scope]);

            if ($value !== '') {
                $css[$scope] = $value;
            }
        }

        return $css;
    }

    /** @return array<string, array{label: string, file: string, group: string}> */
    public function scopes(): array
    {
        return $this->boilerplate->scopes();
    }

    public function defaultCss(string $scope): string
    {
        return $this->boilerplate->css($scope);
    }

    public function assetUrl(string $scope): string
    {
        return $this->boilerplate->assetUrl($scope);
    }

    public function editorCss(string $scope): string
    {
        if ($scope === 'global') {
            $stored = get_option(self::OPTION_CUSTOM_CSS, null);

            return is_string($stored) && $stored !== ''
                ? $this->cssSanitizer->sanitize($stored)
                : $this->defaultCss($scope);
        }

        $stored = get_option(self::OPTION_SCOPED_CSS, null);

        if (is_array($stored) && array_key_exists($scope, $stored) && is_scalar($stored[$scope])) {
            $value = $this->cssSanitizer->sanitize((string) $stored[$scope]);

            if ($value !== '') {
                return $value;
            }
        }

        return $this->defaultCss($scope);
    }

    public function sanitizeCss(mixed $value): string
    {
        $css = $this->cssSanitizer->sanitize($value);

        return $css === trim($this->defaultCss('global')) ? '' : $css;
    }

    /** @return array<string, string> */
    public function sanitizeScopedCss(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $sanitized = [];

        foreach ($this->boilerplate->scopes() as $scope => $definition) {
            if ($scope === 'global' || !array_key_exists($scope, $value) || !is_scalar($value[$scope])) {
                continue;
            }

            $css = $this->cssSanitizer->sanitize((string) $value[$scope]);

            if ($css !== '' && $css !== trim($this->defaultCss($scope))) {
                $sanitized[$scope] = $css;
            }
        }

        return $sanitized;
    }

    public function resetCss(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to reset ADS Tourism CSS.', 'ads-tourism'));
        }

        check_admin_referer(self::ACTION_RESET_CSS, self::RESET_NONCE_FIELD);
        $confirmation = $_POST[self::RESET_CONFIRMATION_FIELD] ?? '';

        if (!is_scalar($confirmation) || (string) wp_unslash($confirmation) !== '1') {
            wp_die(esc_html__('Confirm the CSS reset before continuing.', 'ads-tourism'));
        }

        delete_option(self::OPTION_CUSTOM_CSS);
        delete_option(self::OPTION_SCOPED_CSS);
        update_option(self::OPTION_LOAD_STYLES, true, false);

        wp_safe_redirect(add_query_arg([
            'page' => WorkflowSettings::PAGE_SLUG,
            'ads_tourism_css' => 'reset',
        ], admin_url('admin.php')));
        exit;
    }

    public function renderResetNotice(): void
    {
        if (($_GET['page'] ?? '') !== WorkflowSettings::PAGE_SLUG || ($_GET['ads_tourism_css'] ?? '') !== 'reset') {
            return;
        }

        echo '<div class="notice notice-success is-dismissible"><p>';
        echo esc_html__('ADS Tourism CSS has been reset to the current bundled defaults.', 'ads-tourism');
        echo '</p></div>';
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Use the bundled CSS as a commented starting point, then customize global, content-type, and widget presentation. Disable fallback styles only when your theme or builder supplies all presentation.',
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

    public function renderGlobalCssField(): void
    {
        $this->renderCssEditor('global', self::OPTION_CUSTOM_CSS, $this->editorCss('global'));
    }

    public function renderScopedCssFields(): void
    {
        echo '<p class="description">' . esc_html__(
            'Each editor starts with valid boilerplate CSS. The selectors are grouped by content type and shortcode widget so you can see what to customize.',
            'ads-tourism',
        ) . '</p>';

        foreach (['content' => __('Content types', 'ads-tourism'), 'widget' => __('Shortcode widgets', 'ads-tourism')] as $group => $label) {
            echo '<h3>' . esc_html($label) . '</h3>';

            foreach ($this->boilerplate->scopes() as $scope => $definition) {
                if ($definition['group'] !== $group) {
                    continue;
                }

                $this->renderCssEditor($scope, self::OPTION_SCOPED_CSS . '[' . $scope . ']', $this->editorCss($scope));
            }
        }
    }

    public function renderResetField(): void
    {
        echo '<p class="description">' . esc_html__(
            'Warning: this removes all saved ADS Tourism global, content-type, and widget CSS and re-enables the bundled stylesheet. Theme and builder CSS will not be changed.',
            'ads-tourism',
        ) . '</p>';
        echo '<label><input type="checkbox" name="' . esc_attr(self::RESET_CONFIRMATION_FIELD) . '" value="1"> ';
        echo esc_html__('I understand that my saved ADS Tourism CSS will be removed.', 'ads-tourism');
        echo '</label><br>';
        echo wp_nonce_field(self::ACTION_RESET_CSS, self::RESET_NONCE_FIELD, false, false);
        echo '<button type="submit" class="button button-secondary" name="action" value="'
            . esc_attr(self::ACTION_RESET_CSS) . '" formaction="' . esc_url(admin_url('admin-post.php'))
            . '" formmethod="post" onclick="return window.confirm(\''
            . esc_js(__('Reset all ADS Tourism CSS to the bundled defaults? This cannot be undone.', 'ads-tourism'))
            . '\');">' . esc_html__('Reset ADS Tourism CSS', 'ads-tourism') . '</button>';
    }

    private function renderCssEditor(string $scope, string $name, string $value): void
    {
        $definition = $this->boilerplate->scopes()[$scope] ?? null;

        if (!is_array($definition)) {
            return;
        }

        echo '<details class="ads-tourism-css-editor" open><summary><strong>' . esc_html($definition['label'])
            . '</strong></summary><textarea class="large-text code" rows="14" name="' . esc_attr($name)
            . '">' . esc_textarea($value) . '</textarea></details>';
    }
}
