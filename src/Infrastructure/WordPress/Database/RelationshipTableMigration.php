<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use wpdb;

final readonly class RelationshipTableMigration
{
    public function __construct(private wpdb $database) {}

    public function up(): void
    {
        if (!function_exists('dbDelta')) {
            // @phpstan-ignore-next-line WordPress supplies this file at runtime.
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tableName = $this->database->prefix . 'ads_tourism_relations';
        $charsetCollate = $this->database->get_charset_collate();
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_post_id bigint(20) unsigned NOT NULL,
            target_post_id bigint(20) unsigned NOT NULL,
            relation_key varchar(64) NOT NULL,
            is_primary tinyint(1) NOT NULL DEFAULT 0,
            sort_order int(11) NOT NULL DEFAULT 0,
            metadata_json longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY source_target_relation (source_post_id, target_post_id, relation_key),
            KEY source_relation (source_post_id, relation_key),
            KEY target_relation (target_post_id, relation_key),
            KEY relation_sort (relation_key, sort_order)
        ) {$charsetCollate};";

        dbDelta($sql);
    }
}
