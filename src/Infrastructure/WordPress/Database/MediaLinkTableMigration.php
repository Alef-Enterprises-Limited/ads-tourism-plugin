<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use wpdb;

final readonly class MediaLinkTableMigration
{
    public function __construct(private wpdb $database) {}

    public function up(): void
    {
        if (!function_exists('dbDelta')) {
            // @phpstan-ignore-next-line WordPress supplies this file at runtime.
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tableName = $this->database->prefix . 'ads_tourism_media_links';
        $charsetCollate = $this->database->get_charset_collate();
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entity_post_id bigint(20) unsigned NOT NULL,
            attachment_id bigint(20) unsigned NULL,
            media_url text NULL,
            url_type varchar(16) NULL,
            media_role varchar(32) NOT NULL DEFAULT 'gallery',
            custom_title text NULL,
            custom_alt_text text NULL,
            custom_caption longtext NULL,
            credit text NULL,
            rights_notice text NULL,
            is_primary tinyint(1) NOT NULL DEFAULT 0,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY entity_order (entity_post_id, sort_order),
            KEY entity_role (entity_post_id, media_role, sort_order),
            KEY attachment (attachment_id)
        ) {$charsetCollate};";

        dbDelta($sql);
    }
}
