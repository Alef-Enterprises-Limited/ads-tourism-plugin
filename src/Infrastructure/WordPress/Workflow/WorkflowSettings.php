<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;

final class WorkflowSettings
{
    public const OPTION_REQUIRE_VERIFICATION = 'ads_tourism_require_verification_before_publish';

    private const PAGE_SLUG = 'ads-tourism-settings';

    private const SETTINGS_GROUP = 'ads_tourism_workflow';

    public function registerMenu(): void
    {
        add_submenu_page(
            AdminMenu::SLUG,
            __('ADS Tourism Settings', 'ads-tourism'),
            __('Settings', 'ads-tourism'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage'],
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::SETTINGS_GROUP, self::OPTION_REQUIRE_VERIFICATION, [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ]);
        add_settings_section(
            'ads_tourism_workflow_section',
            __('Editorial workflow', 'ads-tourism'),
            [$this, 'renderSectionDescription'],
            self::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_REQUIRE_VERIFICATION,
            __('Publication gate', 'ads-tourism'),
            [$this, 'renderPublicationGateField'],
            self::PAGE_SLUG,
            'ads_tourism_workflow_section',
        );
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage ADS Tourism settings.', 'ads-tourism'));
        }

        echo '<div class="wrap"><h1>' . esc_html__('ADS Tourism Settings', 'ads-tourism') . '</h1>';
        echo '<form action="options.php" method="post">';
        settings_fields(self::SETTINGS_GROUP);
        do_settings_sections(self::PAGE_SLUG);
        submit_button();
        echo '</form></div>';
    }

    public function renderSectionDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Control how tourism records move from draft through review and verification to publication.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderPublicationGateField(): void
    {
        $enabled = (bool) get_option(self::OPTION_REQUIRE_VERIFICATION, true);

        echo '<input type="hidden" name="' . esc_attr(self::OPTION_REQUIRE_VERIFICATION) . '" value="0">';
        echo '<label><input type="checkbox" name="' . esc_attr(self::OPTION_REQUIRE_VERIFICATION) . '" value="1" ';
        echo checked($enabled, true, false) . '> ';
        echo esc_html__('Require a verified status before a tourism record can be published.', 'ads-tourism');
        echo '</label>';
    }
}
