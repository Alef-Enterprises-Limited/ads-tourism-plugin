<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance;

use AlefDigitalSolutions\ADSTourism\Application\Maintenance\IntegrityReport;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;

final readonly class MaintenancePage
{
    public const ACTION_REPAIR = 'ads_tourism_repair_integrity';

    public const PAGE_SLUG = 'ads-tourism-maintenance';

    public function __construct(private IntegrityScanner $integrity) {}

    public function registerMenu(): void
    {
        add_submenu_page(
            AdminMenu::SLUG,
            __('ADS Tourism Maintenance', 'ads-tourism'),
            __('Maintenance', 'ads-tourism'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render'],
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to maintain ADS Tourism data.', 'ads-tourism'));
        }

        $report = $this->integrity->scan();
        echo '<div class="wrap"><h1>' . esc_html__('ADS Tourism Maintenance', 'ads-tourism') . '</h1>';

        if (isset($_GET['ads_tourism_repaired'])) {
            $count = absint(wp_unslash($_GET['ads_tourism_repaired']));
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html(sprintf(_n('%d safe integrity issue was repaired.', '%d safe integrity issues were repaired.', $count, 'ads-tourism'), $count));
            echo '</p></div>';
        }

        echo '<p>';
        echo esc_html__(
            'The scan reports orphaned plugin-owned rows, stale WooCommerce mappings, and duplicate external IDs. Repairs never delete tourism posts, Media Library attachments, or WooCommerce Products.',
            'ads-tourism',
        );
        echo '</p>';
        $this->renderReport($report);
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="' . esc_attr(self::ACTION_REPAIR) . '">';
        wp_nonce_field(self::ACTION_REPAIR);
        submit_button(__('Repair safe issues', 'ads-tourism'), 'secondary');
        echo '</form><p class="description">';
        echo esc_html__('Duplicate external IDs require an editor to choose the correct identifier and are never changed automatically.', 'ads-tourism');
        echo '</p></div>';
    }

    public function repair(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to repair ADS Tourism data.', 'ads-tourism'));
        }

        check_admin_referer(self::ACTION_REPAIR);
        $result = $this->integrity->repairSafeIssues();
        $url = add_query_arg(
            'ads_tourism_repaired',
            $result->repairedCount(),
            admin_url('admin.php?page=' . self::PAGE_SLUG),
        );
        wp_safe_redirect($url);
        exit;
    }

    private function renderReport(IntegrityReport $report): void
    {
        echo '<h2>' . esc_html__('Integrity scan', 'ads-tourism') . '</h2>';
        echo '<table class="widefat striped"><thead><tr><th scope="col">';
        echo esc_html__('Check', 'ads-tourism') . '</th><th scope="col">';
        echo esc_html__('Issues', 'ads-tourism') . '</th></tr></thead><tbody>';
        $this->row(__('Orphaned relationships', 'ads-tourism'), $report->orphanedRelationships);
        $this->row(__('Invalid media links', 'ads-tourism'), $report->invalidMediaLinks);
        $this->row(__('Packages mapped to missing Products', 'ads-tourism'), $report->missingMappedProducts);
        $this->row(__('Products mapped to missing Packages', 'ads-tourism'), $report->missingMappedPackages);
        $this->row(__('Duplicate external IDs', 'ads-tourism'), $report->duplicateExternalIds);
        echo '</tbody></table><p><strong>';
        echo $report->isHealthy()
            ? esc_html__('No integrity issues were found.', 'ads-tourism')
            : esc_html(sprintf(__('Total issues found: %d', 'ads-tourism'), $report->issueCount()));
        echo '</strong></p>';
    }

    private function row(string $label, int $count): void
    {
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td>' . esc_html((string) $count) . '</td></tr>';
    }
}
