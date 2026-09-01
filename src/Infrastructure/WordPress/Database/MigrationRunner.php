<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;
use AlefDigitalSolutions\ADSTourism\Plugin;
use Throwable;

final class MigrationRunner
{
    public const FAILURE_OPTION = 'ads_tourism_migration_failure';

    public const LOCK_OPTION = 'ads_tourism_migration_lock';

    public const RETRY_ACTION = 'ads_tourism_retry_migrations';

    public const SCHEMA_OPTION = 'ads_tourism_schema_version';

    private const LOCK_LIFETIME = 300;

    public function __construct(
        private RelationshipTableMigration $relationships,
        private MediaLinkTableMigration $mediaLinks,
        private ImportRunTableMigration $importRuns,
    ) {}

    public function run(): bool
    {
        if ($this->isComplete()) {
            return true;
        }

        if (!$this->acquireLock()) {
            return false;
        }

        try {
            $this->relationships->up();
            $this->mediaLinks->up();
            $this->importRuns->up();
            update_option(self::SCHEMA_OPTION, Plugin::SCHEMA_VERSION, false);

            if (get_option(WorkflowSettings::OPTION_REQUIRE_VERIFICATION, null) === null) {
                add_option(WorkflowSettings::OPTION_REQUIRE_VERIFICATION, true);
            }

            if (get_option(PermalinkSettings::OPTION_REDIRECTS, null) === null) {
                add_option(PermalinkSettings::OPTION_REDIRECTS, ['places' => 'places-to-go'], '', false);
            }

            delete_option(self::FAILURE_OPTION);
            do_action('ads_tourism_migrations_completed', Plugin::SCHEMA_VERSION);

            return true;
        } catch (Throwable $exception) {
            update_option(self::FAILURE_OPTION, [
                'schema_version' => Plugin::SCHEMA_VERSION,
                'exception_type' => $exception::class,
                'failed_at' => gmdate('c'),
            ], false);
            do_action('ads_tourism_migrations_failed', Plugin::SCHEMA_VERSION, $exception::class);

            return false;
        } finally {
            delete_option(self::LOCK_OPTION);
        }
    }

    public function isComplete(): bool
    {
        return $this->installedVersion() >= Plugin::SCHEMA_VERSION && $this->failure() === null;
    }

    public function installedVersion(): int
    {
        return max(0, (int) get_option(self::SCHEMA_OPTION, 0));
    }

    /** @return array{schema_version: int, exception_type: string, failed_at: string}|null */
    public function failure(): ?array
    {
        $failure = get_option(self::FAILURE_OPTION, null);

        if (!is_array($failure)) {
            return null;
        }

        return [
            'schema_version' => max(0, (int) ($failure['schema_version'] ?? 0)),
            'exception_type' => sanitize_text_field((string) ($failure['exception_type'] ?? '')),
            'failed_at' => sanitize_text_field((string) ($failure['failed_at'] ?? '')),
        ];
    }

    public function renderFailureNotice(): void
    {
        if ($this->failure() === null || !current_user_can('manage_options')) {
            return;
        }

        $url = wp_nonce_url(
            admin_url('admin-post.php?action=' . self::RETRY_ACTION),
            self::RETRY_ACTION,
        );
        echo '<div class="notice notice-error"><p><strong>';
        echo esc_html__('ADS Tourism database update is incomplete.', 'ads-tourism');
        echo '</strong> ';
        echo esc_html__(
            'Features that depend on plugin tables may be unavailable until the update succeeds.',
            'ads-tourism',
        );
        echo ' <a href="' . esc_url($url) . '">' . esc_html__('Retry database update', 'ads-tourism') . '</a>';
        echo '</p></div>';
    }

    public function retry(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to update ADS Tourism.', 'ads-tourism'));
        }

        check_admin_referer(self::RETRY_ACTION);
        delete_option(self::FAILURE_OPTION);
        delete_option(self::LOCK_OPTION);
        $success = $this->run();
        $url = add_query_arg(
            'ads_tourism_migration',
            $success ? 'success' : 'failed',
            admin_url('admin.php?page=ads-tourism-system-status'),
        );
        wp_safe_redirect($url);
        exit;
    }

    private function acquireLock(): bool
    {
        $now = time();

        if (add_option(self::LOCK_OPTION, $now, '', false)) {
            return true;
        }

        $lockedAt = (int) get_option(self::LOCK_OPTION, 0);

        if ($lockedAt > 0 && ($now - $lockedAt) < self::LOCK_LIFETIME) {
            return false;
        }

        delete_option(self::LOCK_OPTION);

        return add_option(self::LOCK_OPTION, $now, '', false);
    }
}
