<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance;

final class PrivacyPolicyGuide
{
    public function register(): void
    {
        if (!function_exists('wp_add_privacy_policy_content')) {
            return;
        }

        $content = '<p>' . esc_html__(
            'ADS Tourism stores public destination and tourism-business information entered by site editors. Internal verification notes and import diagnostics are restricted to authorized administrators and are not published through the plugin REST API.',
            'ads-tourism',
        ) . '</p><p>' . esc_html__(
            'Temporary CSV import files and diagnostic rows are removed according to the configured transfer-retention period. If WooCommerce is enabled, WooCommerce separately controls customer, cart, checkout, order, and payment information under its own privacy settings.',
            'ads-tourism',
        ) . '</p>';

        wp_add_privacy_policy_content(__('ADS Tourism', 'ads-tourism'), wp_kses_post($content));
    }
}
