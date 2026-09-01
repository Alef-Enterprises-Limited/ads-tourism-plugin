<?php

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once __DIR__ . '/src/Support/Autoloader.php';

\AlefDigitalSolutions\ADSTourism\Support\Autoloader::register();

$settings = get_option('ads_tourism_maintenance_settings', []);
$policy = new \AlefDigitalSolutions\ADSTourism\Domain\Maintenance\DataRetentionPolicy();
$deleteData = is_array($settings) && $policy->deletionIsConfirmed(
    rest_sanitize_boolean($settings['delete_data_on_uninstall'] ?? false),
    \AlefDigitalSolutions\ADSTourism\Domain\Maintenance\DataRetentionPolicy::CONFIRMATION,
);

// Preservation is the default and requires no cleanup work.
if (!$deleteData) {
    return;
}

global $wpdb;

/** @var wpdb $wpdb */
$postTypes = [
    'ads_place',
    'ads_activity',
    'ads_stay',
    'ads_operator',
    'ads_package',
];
$taxonomies = [
    'ads_geographic_area',
    'ads_place_type',
    'ads_activity_type',
    'ads_stay_type',
    'ads_package_type',
    'ads_amenity',
    'ads_traveller',
    'ads_accessibility',
    'ads_tourism_tag',
];

// Remove reciprocal mapping metadata without deleting any WooCommerce Product.
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
    '_ads_tourism_package_id',
));

$postIds = get_posts([
    'post_type' => $postTypes,
    'post_status' => 'any',
    'numberposts' => -1,
    'fields' => 'ids',
    'orderby' => 'ID',
    'order' => 'ASC',
]);

foreach ($postIds as $postId) {
    wp_delete_post(absint($postId), true);
}

foreach ($taxonomies as $taxonomy) {
    if (!taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, $postTypes, ['public' => false]);
    }

    $termIds = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'fields' => 'ids',
    ]);

    if (!is_array($termIds)) {
        continue;
    }

    foreach ($termIds as $termId) {
        $resolvedTermId = $termId instanceof WP_Term ? $termId->term_id : absint($termId);
        wp_delete_term($resolvedTermId, $taxonomy);
    }
}

foreach (['relations', 'media_links', 'import_runs'] as $table) {
    $tableName = $wpdb->prefix . 'ads_tourism_' . $table;
    $wpdb->query("DROP TABLE IF EXISTS {$tableName}");
}

$options = [
    'ads_tourism_version',
    'ads_tourism_schema_version',
    'ads_tourism_migration_failure',
    'ads_tourism_migration_lock',
    'ads_tourism_require_verification_before_publish',
    'ads_tourism_transfer_settings',
    'ads_tourism_woocommerce_settings',
    'ads_tourism_maintenance_settings',
    'ads_tourism_google_maps_browser_key',
    'ads_tourism_map_provider',
    'ads_tourism_default_images',
    'ads_tourism_multilingual_fallback_to_original',
    'ads_tourism_permalink_bases',
    'ads_tourism_permalink_base_redirects',
    'ads_tourism_custom_css',
    'ads_tourism_load_frontend_styles',
    'ads_tourism_query_cache_generation',
    'ads_tourism_schema_mode',
    'ads_tourism_native_social_metadata',
];

foreach ($options as $option) {
    delete_option($option);
}

$wpdb->query(
    "DELETE FROM {$wpdb->options}
    WHERE option_name LIKE '_transient_ads_tourism_%'
        OR option_name LIKE '_transient_timeout_ads_tourism_%'",
);

wp_clear_scheduled_hook('ads_tourism_cleanup_transfers');
