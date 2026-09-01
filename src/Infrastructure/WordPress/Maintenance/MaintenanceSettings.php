<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance;

use AlefDigitalSolutions\ADSTourism\Domain\Maintenance\DataRetentionPolicy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final class MaintenanceSettings
{
    public const OPTION = 'ads_tourism_maintenance_settings';

    public function __construct(private readonly DataRetentionPolicy $policy = new DataRetentionPolicy()) {}

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION, [
            'type' => 'object',
            'default' => $this->defaults(),
            'sanitize_callback' => [$this, 'sanitize'],
        ]);
        add_settings_section(
            'ads_tourism_data_lifecycle',
            __('Data lifecycle', 'ads-tourism'),
            [$this, 'renderDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION,
            __('Uninstall behavior', 'ads-tourism'),
            [$this, 'renderField'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_data_lifecycle',
        );
    }

    /** @return array{delete_data_on_uninstall: bool} */
    public function sanitize(mixed $value): array
    {
        $value = is_array($value) ? $value : [];
        $deleteRequested = rest_sanitize_boolean($value['delete_data_on_uninstall'] ?? false);
        $confirmation = sanitize_text_field((string) ($value['confirmation'] ?? ''));

        $confirmed = $this->policy->deletionIsConfirmed($deleteRequested, $confirmation);

        if ($deleteRequested && !$confirmed) {
            add_settings_error(
                self::OPTION,
                'ads_tourism_uninstall_confirmation',
                sprintf(
                    __('Data deletion was not enabled. Enter %s exactly to confirm.', 'ads-tourism'),
                    DataRetentionPolicy::CONFIRMATION,
                ),
            );
        }

        return [
            'delete_data_on_uninstall' => $confirmed,
        ];
    }

    public function deleteDataOnUninstall(): bool
    {
        $value = get_option(self::OPTION, $this->defaults());

        return is_array($value) && rest_sanitize_boolean($value['delete_data_on_uninstall'] ?? false);
    }

    public function renderDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Deactivation always preserves tourism data. Destructive uninstall is disabled unless an administrator explicitly confirms it here before uninstalling.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderField(): void
    {
        $enabled = $this->deleteDataOnUninstall();
        $option = esc_attr(self::OPTION);

        echo '<input type="hidden" name="' . $option . '[delete_data_on_uninstall]" value="0">';
        echo '<label><input type="checkbox" name="' . $option . '[delete_data_on_uninstall]" value="1" ';
        echo checked($enabled, true, false) . '> ';
        echo esc_html__('Delete ADS Tourism records and plugin-owned data during uninstall.', 'ads-tourism');
        echo '</label>';
        echo '<p class="description">';
        echo esc_html__(
            'Shared Media Library attachments and WooCommerce Products are never deleted. This action cannot be undone.',
            'ads-tourism',
        );
        echo '</p><label for="ads-tourism-uninstall-confirmation">';
        echo esc_html__('Confirmation phrase', 'ads-tourism') . '</label><br>';
        echo '<input id="ads-tourism-uninstall-confirmation" type="text" class="regular-text" autocomplete="off" name="';
        echo $option . '[confirmation]" value="" placeholder="' . esc_attr(DataRetentionPolicy::CONFIRMATION) . '">';
    }

    /** @return array{delete_data_on_uninstall: bool} */
    private function defaults(): array
    {
        return ['delete_data_on_uninstall' => false];
    }
}
