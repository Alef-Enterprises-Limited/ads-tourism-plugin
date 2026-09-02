<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use wpdb;

final readonly class LocationTableMigration
{
    public function __construct(private wpdb $database) {}

    public function up(): void
    {
        if (!function_exists('dbDelta')) {
            // @phpstan-ignore-next-line WordPress supplies this file at runtime.
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $tableName = $this->database->prefix . 'ads_tourism_locations';
        $charsetCollate = $this->database->get_charset_collate();
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            entity_post_id bigint(20) unsigned NOT NULL,
            label varchar(255) NOT NULL DEFAULT '',
            location_role varchar(32) NOT NULL DEFAULT 'primary',
            latitude decimal(10,7) NOT NULL,
            longitude decimal(10,7) NOT NULL,
            is_primary tinyint(1) NOT NULL DEFAULT 0,
            show_on_map tinyint(1) NOT NULL DEFAULT 1,
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY entity_location (entity_post_id, label, location_role, latitude, longitude),
            KEY entity_order (entity_post_id, sort_order),
            KEY entity_map (entity_post_id, show_on_map, sort_order)
        ) {$charsetCollate};";

        dbDelta($sql);
        $this->migrateLegacyCoordinates($tableName);
    }

    private function migrateLegacyCoordinates(string $tableName): void
    {
        $posts = $this->database->posts;
        $postmeta = $this->database->postmeta;
        $now = gmdate('Y-m-d H:i:s');
        $query = $this->database->prepare(
            "INSERT IGNORE INTO {$tableName}
                (entity_post_id, label, location_role, latitude, longitude, is_primary, show_on_map, sort_order, created_at, updated_at)
            SELECT p.ID,
                CASE WHEN p.post_type = 'ads_package' THEN 'Meeting point' ELSE 'Primary location' END,
                CASE WHEN p.post_type = 'ads_package' THEN 'meeting_point' ELSE 'primary' END,
                CAST(latitude.meta_value AS DECIMAL(10,7)),
                CAST(longitude.meta_value AS DECIMAL(10,7)),
                1, 1, 0, %s, %s
            FROM {$posts} p
            INNER JOIN {$postmeta} latitude ON latitude.post_id = p.ID
                AND latitude.meta_key = CASE WHEN p.post_type = 'ads_package'
                    THEN 'ads_tourism_meeting_point_latitude' ELSE 'ads_tourism_latitude' END
            INNER JOIN {$postmeta} longitude ON longitude.post_id = p.ID
                AND longitude.meta_key = CASE WHEN p.post_type = 'ads_package'
                    THEN 'ads_tourism_meeting_point_longitude' ELSE 'ads_tourism_longitude' END
            WHERE p.post_type IN ('ads_place', 'ads_activity', 'ads_stay', 'ads_operator', 'ads_package')
                AND CAST(latitude.meta_value AS DECIMAL(10,7)) BETWEEN -90 AND 90
                AND CAST(longitude.meta_value AS DECIMAL(10,7)) BETWEEN -180 AND 180",
            $now,
            $now,
        );

        $this->database->query($query);
    }
}
