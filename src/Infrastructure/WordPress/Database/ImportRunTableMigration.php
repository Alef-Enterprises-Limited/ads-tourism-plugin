<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use wpdb;

final readonly class ImportRunTableMigration
{
    public function __construct(private wpdb $database) {}

    public function up(): void
    {
        if (!function_exists('dbDelta')) {
            // @phpstan-ignore-next-line WordPress supplies this file at runtime.
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tableName = $this->database->prefix . 'ads_tourism_import_runs';
        $charsetCollate = $this->database->get_charset_collate();
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            record_type varchar(32) NOT NULL,
            schema_version varchar(16) NOT NULL,
            status varchar(20) NOT NULL,
            duplicate_policy varchar(20) NOT NULL DEFAULT 'skip',
            taxonomy_mode varchar(20) NOT NULL DEFAULT 'simple',
            allow_term_creation tinyint(1) NOT NULL DEFAULT 0,
            source_filename varchar(255) NOT NULL,
            source_path text NOT NULL,
            rejected_path text NULL,
            delimiter varchar(4) NOT NULL DEFAULT ',',
            mapping_json longtext NULL,
            total_rows int(11) unsigned NOT NULL DEFAULT 0,
            processed_rows int(11) unsigned NOT NULL DEFAULT 0,
            imported_rows int(11) unsigned NOT NULL DEFAULT 0,
            updated_rows int(11) unsigned NOT NULL DEFAULT 0,
            skipped_rows int(11) unsigned NOT NULL DEFAULT 0,
            rejected_rows int(11) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            started_at datetime NULL,
            completed_at datetime NULL,
            PRIMARY KEY  (id),
            KEY user_created (user_id, created_at),
            KEY status_created (status, created_at)
        ) {$charsetCollate};";

        dbDelta($sql);
    }
}
