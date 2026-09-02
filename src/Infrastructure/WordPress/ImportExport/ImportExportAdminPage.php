<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRun;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;

final readonly class ImportExportAdminPage
{
    public const SLUG = 'ads-tourism-transfer';

    public function __construct(
        private ImportRunRepository $runs,
        private TransferSettings $settings,
        private string $pluginFile,
    ) {}

    public function registerMenu(): void
    {
        add_submenu_page(
            AdminMenu::SLUG,
            __('CSV Import and Export', 'ads-tourism'),
            __('CSV Import/Export', 'ads-tourism'),
            'edit_others_posts',
            self::SLUG,
            [$this, 'render'],
        );
    }

    public function enqueueAssets(): void
    {
        if (($_GET['page'] ?? '') !== self::SLUG) {
            return;
        }

        wp_enqueue_style(
            'ads-tourism-transfer',
            plugins_url('assets/admin/import-export.css', $this->pluginFile),
            [],
            \AlefDigitalSolutions\ADSTourism\Plugin::VERSION,
        );
        wp_enqueue_script(
            'ads-tourism-transfer',
            plugins_url('assets/admin/import-export.js', $this->pluginFile),
            [],
            \AlefDigitalSolutions\ADSTourism\Plugin::VERSION,
            true,
        );
        wp_localize_script('ads-tourism-transfer', 'adsTourismTransfer', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(CsvImportController::NONCE_ACTION),
            'actions' => [
                'upload' => CsvImportController::ACTION_UPLOAD,
                'preview' => CsvImportController::ACTION_PREVIEW,
                'batch' => CsvImportController::ACTION_BATCH,
            ],
            'messages' => [
                'working' => __('Working…', 'ads-tourism'),
                'complete' => __('Import complete.', 'ads-tourism'),
                'requestFailed' => __('The request failed. Try again or check the server logs.', 'ads-tourism'),
            ],
        ]);
    }

    public function render(): void
    {
        if (!current_user_can('edit_others_posts')) {
            wp_die(esc_html__('You do not have permission to transfer tourism records.', 'ads-tourism'));
        }

        echo '<div class="wrap ads-tourism-transfer">';
        echo '<h1>' . esc_html__('CSV Import and Export', 'ads-tourism') . '</h1>';
        echo '<p>' . esc_html__(
            'Use versioned CSV templates to validate data before writing. New records are always Draft and Unverified.',
            'ads-tourism',
        ) . '</p>';
        $this->renderLocationNotice();
        $this->renderImport();
        $this->renderExport();
        $this->renderHistory();

        if (current_user_can('manage_options')) {
            $this->renderSettings();
        }

        echo '</div>';
    }

    private function renderImport(): void
    {
        echo '<section class="ads-tourism-transfer__panel">';
        echo '<h2>' . esc_html__('Import records', 'ads-tourism') . '</h2>';
        echo '<p>' . sprintf(
            /* translators: %s is the CSV marker that clears an existing value. */
            esc_html__('Blank cells leave existing values unchanged during updates. Use %s to remove a value.', 'ads-tourism'),
            esc_html(CsvSchema::CLEAR_VALUE),
        ) . '</p>';
        echo '<div class="ads-tourism-transfer__templates"><strong>'
            . esc_html__('Download templates:', 'ads-tourism') . '</strong> ';

        foreach (ContentType::cases() as $contentType) {
            $url = wp_nonce_url(add_query_arg([
                'action' => CsvDownloadController::ACTION_TEMPLATE,
                'record_type' => $contentType->value,
            ], admin_url('admin-post.php')), CsvDownloadController::NONCE_ACTION);
            echo '<a class="button button-small" href="' . esc_url($url) . '">'
                . esc_html($this->label($contentType)) . '</a> ';
        }

        echo '</div>';
        $locationsTemplateUrl = wp_nonce_url(add_query_arg([
            'action' => CsvDownloadController::ACTION_LOCATIONS_TEMPLATE,
        ], admin_url('admin-post.php')), CsvDownloadController::NONCE_ACTION);
        echo '<p><a class="button button-small" href="' . esc_url($locationsTemplateUrl) . '">'
            . esc_html__('Locations CSV template', 'ads-tourism') . '</a></p>';
        echo '<p class="description">' . esc_html__(
            'Locations are transferred in their own CSV. The external_id and record_type identify the existing record; importing rows replaces all locations for each record included in the file.',
            'ads-tourism',
        ) . '</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="' . esc_attr(LocationCsvImportController::ACTION) . '">';
        wp_nonce_field(LocationCsvImportController::NONCE_ACTION);
        echo '<label><span>' . esc_html__('Locations CSV file', 'ads-tourism') . '</span> '
            . '<input type="file" name="locations_csv" accept=".csv,text/csv" required></label> ';
        echo '<button type="submit" class="button">' . esc_html__('Import locations', 'ads-tourism') . '</button>';
        echo '</form>';
        echo '<form id="ads-tourism-import-upload" enctype="multipart/form-data">';
        echo '<label><span>' . esc_html__('Record type', 'ads-tourism') . '</span>';
        echo '<select name="record_type" required>';

        foreach (ContentType::cases() as $contentType) {
            echo '<option value="' . esc_attr($contentType->value) . '">'
                . esc_html($this->label($contentType)) . '</option>';
        }

        echo '</select></label>';
        echo '<label><span>' . esc_html__('UTF-8 CSV file', 'ads-tourism') . '</span>';
        echo '<input type="file" name="csv_file" accept=".csv,text/csv" required></label>';
        echo '<button type="submit" class="button button-primary">'
            . esc_html__('Upload and map columns', 'ads-tourism') . '</button>';
        echo '</form>';
        echo '<div id="ads-tourism-import-config" hidden>';
        echo '<h3>' . esc_html__('Column mapping', 'ads-tourism') . '</h3>';
        echo '<p id="ads-tourism-import-summary"></p>';
        echo '<div class="ads-tourism-transfer__table-wrap"><table class="widefat striped">';
        echo '<thead><tr><th>' . esc_html__('CSV column', 'ads-tourism') . '</th><th>'
            . esc_html__('Tourism field', 'ads-tourism') . '</th></tr></thead>';
        echo '<tbody id="ads-tourism-mapping"></tbody></table></div>';
        echo '<div class="ads-tourism-transfer__options">';
        echo '<label><span>' . esc_html__('Duplicate external ID', 'ads-tourism') . '</span>';
        echo '<select id="ads-tourism-duplicate-policy">';
        echo '<option value="skip">' . esc_html__('Skip existing record', 'ads-tourism') . '</option>';
        echo '<option value="update">' . esc_html__('Update existing record', 'ads-tourism') . '</option>';
        echo '<option value="create_new">' . esc_html__('Create with a new external ID', 'ads-tourism') . '</option>';
        echo '</select></label>';
        echo '<label><span>' . esc_html__('Taxonomy import', 'ads-tourism') . '</span>';
        echo '<select id="ads-tourism-taxonomy-mode">';
        echo '<option value="simple">' . esc_html__('Simple — fields only', 'ads-tourism') . '</option>';
        echo '<option value="advanced">' . esc_html__('Advanced — controlled term slugs', 'ads-tourism') . '</option>';
        echo '</select></label>';

        if ($this->settings->allowTermCreation() && current_user_can('manage_categories')) {
            echo '<label class="ads-tourism-transfer__checkbox"><input type="checkbox" id="ads-tourism-create-terms"> '
                . esc_html__('Create unknown taxonomy terms after preview', 'ads-tourism') . '</label>';
        }

        echo '</div>';
        echo '<button type="button" class="button" id="ads-tourism-preview">'
            . esc_html__('Validate and preview', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button button-primary" id="ads-tourism-start" disabled>'
            . esc_html__('Start import', 'ads-tourism') . '</button>';
        echo '</div>';
        echo '<div id="ads-tourism-preview-results" aria-live="polite"></div>';
        echo '<div id="ads-tourism-import-progress" aria-live="polite"></div>';
        echo '</section>';
    }

    private function renderLocationNotice(): void
    {
        $status = isset($_GET['ads_tourism_locations']) && is_scalar($_GET['ads_tourism_locations'])
            ? sanitize_key((string) wp_unslash($_GET['ads_tourism_locations']))
            : '';

        if ($status !== 'success') {
            return;
        }

        $count = isset($_GET['count']) && is_scalar($_GET['count']) ? absint($_GET['count']) : 0;
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(
            /* translators: %d is the number of imported location rows. */
            _n('%d location row imported.', '%d location rows imported.', $count, 'ads-tourism'),
            $count,
        )) . '</p></div>';
    }

    private function renderExport(): void
    {
        echo '<section class="ads-tourism-transfer__panel">';
        echo '<h2>' . esc_html__('Export records', 'ads-tourism') . '</h2>';
        echo '<p>' . esc_html__(
            'The ZIP contains record CSV files, taxonomy definitions, relationships, media links, and a checksummed manifest.',
            'ads-tourism',
        ) . '</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="' . esc_attr(CsvDownloadController::ACTION_EXPORT) . '">';
        wp_nonce_field(CsvDownloadController::NONCE_ACTION);
        echo '<div class="ads-tourism-transfer__options">';
        echo '<label><span>' . esc_html__('Record type', 'ads-tourism') . '</span><select name="record_type">';
        echo '<option value="">' . esc_html__('All record types', 'ads-tourism') . '</option>';

        foreach (ContentType::cases() as $contentType) {
            echo '<option value="' . esc_attr($contentType->value) . '">'
                . esc_html($this->label($contentType)) . '</option>';
        }

        echo '</select></label>';
        echo '<label><span>' . esc_html__('WordPress status', 'ads-tourism') . '</span><select name="post_status">';
        echo '<option value="">' . esc_html__('Any status', 'ads-tourism') . '</option>';

        $postStatuses = [
            'draft' => __('Draft', 'ads-tourism'),
            'pending' => __('Pending', 'ads-tourism'),
            'publish' => __('Published', 'ads-tourism'),
            'private' => __('Private', 'ads-tourism'),
        ];

        foreach ($postStatuses as $value => $label) {
            echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
        }

        echo '</select></label>';
        echo '<label><span>' . esc_html__('Verification status', 'ads-tourism') . '</span>';
        echo '<select name="verification_status"><option value="">' . esc_html__('Any verification status', 'ads-tourism') . '</option>';

        foreach (VerificationStatus::labels() as $status => $label) {
            echo '<option value="' . esc_attr($status) . '">' . esc_html(__($label, 'ads-tourism')) . '</option>';
        }

        echo '</select></label>';
        echo '<label><span>' . esc_html__('Modified after', 'ads-tourism') . '</span><input type="date" name="modified_after"></label>';
        echo '<label><span>' . esc_html__('Modified before', 'ads-tourism') . '</span><input type="date" name="modified_before"></label>';
        echo '<label><span>' . esc_html__('Selected post IDs', 'ads-tourism') . '</span>';
        echo '<input type="text" name="selected_post_ids" placeholder="12, 34, 56"></label>';
        echo '</div>';
        echo '<button type="submit" class="button button-primary">' . esc_html__('Download export ZIP', 'ads-tourism') . '</button>';
        echo '</form></section>';
    }

    private function renderHistory(): void
    {
        echo '<section class="ads-tourism-transfer__panel"><h2>' . esc_html__('Recent imports', 'ads-tourism') . '</h2>';
        echo '<div class="ads-tourism-transfer__table-wrap"><table class="widefat striped">';
        echo '<thead><tr><th>' . esc_html__('File', 'ads-tourism') . '</th><th>'
            . esc_html__('Type', 'ads-tourism') . '</th><th>' . esc_html__('Status', 'ads-tourism') . '</th><th>'
            . esc_html__('Processed', 'ads-tourism') . '</th><th>' . esc_html__('Created / Updated / Skipped / Rejected', 'ads-tourism')
            . '</th><th>' . esc_html__('Report', 'ads-tourism') . '</th></tr></thead><tbody>';
        $runs = $this->runs->recent();

        if ($runs === []) {
            echo '<tr><td colspan="6">' . esc_html__('No CSV imports have been started.', 'ads-tourism') . '</td></tr>';
        }

        foreach ($runs as $run) {
            $this->renderHistoryRow($run);
        }

        echo '</tbody></table></div></section>';
    }

    private function renderHistoryRow(ImportRun $run): void
    {
        echo '<tr><td>' . esc_html($run->sourceFilename) . '</td><td>'
            . esc_html($this->label($run->contentType)) . '</td><td>'
            . esc_html(ucwords(str_replace('_', ' ', $run->status->value))) . '</td><td>'
            . esc_html($run->processedRows . ' / ' . $run->totalRows) . '</td><td>'
            . esc_html(implode(' / ', [
                (string) $run->importedRows,
                (string) $run->updatedRows,
                (string) $run->skippedRows,
                (string) $run->rejectedRows,
            ])) . '</td><td>';

        if ($run->rejectedRows > 0) {
            $url = wp_nonce_url(add_query_arg([
                'action' => CsvDownloadController::ACTION_REJECTED,
                'run_id' => (string) $run->id,
            ], admin_url('admin-post.php')), CsvDownloadController::NONCE_ACTION);
            echo '<a href="' . esc_url($url) . '">' . esc_html__('Download rejected rows', 'ads-tourism') . '</a>';
        } else {
            echo '&mdash;';
        }

        echo '</td></tr>';
    }

    private function renderSettings(): void
    {
        $values = $this->settings->values();
        echo '<section class="ads-tourism-transfer__panel"><h2>' . esc_html__('Transfer settings', 'ads-tourism') . '</h2>';
        echo '<form method="post" action="options.php">';
        settings_fields('ads_tourism_transfers');
        echo '<div class="ads-tourism-transfer__options">';

        foreach ([
            'maximum_upload_mb' => __('Maximum upload size (MB)', 'ads-tourism'),
            'batch_size' => __('Rows per AJAX batch', 'ads-tourism'),
            'retention_hours' => __('Temporary-file retention (hours)', 'ads-tourism'),
        ] as $key => $label) {
            echo '<label><span>' . esc_html($label) . '</span><input type="number" min="1" name="'
                . esc_attr(TransferSettings::OPTION . '[' . $key . ']') . '" value="'
                . esc_attr((string) $values[$key]) . '"></label>';
        }

        echo '<label class="ads-tourism-transfer__checkbox"><input type="checkbox" name="'
            . esc_attr(TransferSettings::OPTION . '[allow_term_creation]') . '" value="1" '
            . checked((bool) $values['allow_term_creation'], true, false) . '> '
            . esc_html__('Allow administrators to create unknown taxonomy terms during import', 'ads-tourism') . '</label>';
        echo '</div>';
        submit_button(__('Save transfer settings', 'ads-tourism'));
        echo '</form></section>';
    }

    private function label(ContentType $contentType): string
    {
        return match ($contentType) {
            ContentType::PLACE => __('Places to Go', 'ads-tourism'),
            ContentType::ACTIVITY => __('Things to Do', 'ads-tourism'),
            ContentType::STAY => __('Places to Stay', 'ads-tourism'),
            ContentType::OPERATOR => __('Tour Operators', 'ads-tourism'),
            ContentType::PACKAGE => __('Packages', 'ads-tourism'),
        };
    }
}
