<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance;

use AlefDigitalSolutions\ADSTourism\Application\Maintenance\IntegrityRepairResult;
use AlefDigitalSolutions\ADSTourism\Application\Maintenance\IntegrityReport;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WooCommerceProductGateway;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WordPressPackageProductLinkStore;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\WpdbMediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\WpdbRelationshipRepository;
use wpdb;

final readonly class IntegrityScanner
{
    public function __construct(
        private wpdb $database,
        private WpdbRelationshipRepository $relationships,
        private WpdbMediaLinkRepository $mediaLinks,
    ) {}

    public function scan(): IntegrityReport
    {
        return new IntegrityReport(
            $this->orphanedRelationships(),
            $this->invalidMediaLinks(),
            $this->missingMappedProducts(),
            $this->missingMappedPackages(),
            $this->duplicateExternalIds(),
        );
    }

    public function repairSafeIssues(): IntegrityRepairResult
    {
        $relationships = $this->relationships->deleteOrphans();
        $mediaLinks = $this->mediaLinks->deleteOrphans();
        $packageMappings = $this->deleteMissingPackageProductMappings();
        $productMappings = $this->deleteMissingProductPackageMappings();

        do_action('ads_tourism_integrity_repaired', [
            'relationships_removed' => $relationships,
            'media_links_removed' => $mediaLinks,
            'package_mappings_detached' => $packageMappings,
            'product_mappings_detached' => $productMappings,
        ]);

        return new IntegrityRepairResult($relationships, $mediaLinks, $packageMappings, $productMappings);
    }

    private function orphanedRelationships(): int
    {
        $relations = $this->relationships->tableName();
        $posts = $this->database->posts;

        return $this->count("SELECT COUNT(*) FROM {$relations} AS relation_row
            LEFT JOIN {$posts} AS source_post ON source_post.ID = relation_row.source_post_id
            LEFT JOIN {$posts} AS target_post ON target_post.ID = relation_row.target_post_id
            WHERE source_post.ID IS NULL OR target_post.ID IS NULL");
    }

    private function invalidMediaLinks(): int
    {
        $media = $this->mediaLinks->tableName();
        $posts = $this->database->posts;

        return $this->count("SELECT COUNT(*) FROM {$media} AS media_row
            LEFT JOIN {$posts} AS entity_post ON entity_post.ID = media_row.entity_post_id
            LEFT JOIN {$posts} AS attachment_post ON attachment_post.ID = media_row.attachment_id
            WHERE entity_post.ID IS NULL
                OR (media_row.attachment_id IS NOT NULL AND attachment_post.ID IS NULL)");
    }

    private function missingMappedProducts(): int
    {
        $postmeta = $this->database->postmeta;
        $posts = $this->database->posts;
        $metaKey = WordPressPackageProductLinkStore::PACKAGE_PRODUCT_META;
        $query = $this->database->prepare(
            "SELECT COUNT(*) FROM {$postmeta} AS mapping
            LEFT JOIN {$posts} AS product_post ON product_post.ID = CAST(mapping.meta_value AS UNSIGNED)
                AND product_post.post_type = 'product'
            WHERE mapping.meta_key = %s AND product_post.ID IS NULL",
            $metaKey,
        );

        return $this->count($query);
    }

    private function missingMappedPackages(): int
    {
        $postmeta = $this->database->postmeta;
        $posts = $this->database->posts;
        $metaKey = WooCommerceProductGateway::PRODUCT_PACKAGE_META;
        $query = $this->database->prepare(
            "SELECT COUNT(*) FROM {$postmeta} AS mapping
            LEFT JOIN {$posts} AS package_post ON package_post.ID = CAST(mapping.meta_value AS UNSIGNED)
                AND package_post.post_type = 'ads_package'
            WHERE mapping.meta_key = %s AND package_post.ID IS NULL",
            $metaKey,
        );

        return $this->count($query);
    }

    private function duplicateExternalIds(): int
    {
        $postmeta = $this->database->postmeta;
        $posts = $this->database->posts;
        $types = "'ads_place', 'ads_activity', 'ads_stay', 'ads_operator', 'ads_package'";
        $query = $this->database->prepare(
            "SELECT COUNT(*) FROM (
                SELECT external.meta_value
                FROM {$postmeta} AS external
                INNER JOIN {$posts} AS tourism_post ON tourism_post.ID = external.post_id
                WHERE external.meta_key = %s
                    AND external.meta_value <> ''
                    AND tourism_post.post_type IN ({$types})
                GROUP BY external.meta_value
                HAVING COUNT(*) > 1
            ) AS duplicates",
            'ads_tourism_external_id',
        );

        return $this->count($query);
    }

    private function deleteMissingPackageProductMappings(): int
    {
        $postmeta = $this->database->postmeta;
        $posts = $this->database->posts;
        $query = $this->database->prepare(
            "DELETE mapping FROM {$postmeta} AS mapping
            LEFT JOIN {$posts} AS product_post ON product_post.ID = CAST(mapping.meta_value AS UNSIGNED)
                AND product_post.post_type = 'product'
            WHERE mapping.meta_key = %s AND product_post.ID IS NULL",
            WordPressPackageProductLinkStore::PACKAGE_PRODUCT_META,
        );

        return $this->affectedRows($query);
    }

    private function deleteMissingProductPackageMappings(): int
    {
        $postmeta = $this->database->postmeta;
        $posts = $this->database->posts;
        $query = $this->database->prepare(
            "DELETE mapping FROM {$postmeta} AS mapping
            LEFT JOIN {$posts} AS package_post ON package_post.ID = CAST(mapping.meta_value AS UNSIGNED)
                AND package_post.post_type = 'ads_package'
            WHERE mapping.meta_key = %s AND package_post.ID IS NULL",
            WooCommerceProductGateway::PRODUCT_PACKAGE_META,
        );

        return $this->affectedRows($query);
    }

    private function count(string $query): int
    {
        return max(0, (int) $this->database->get_var($query));
    }

    private function affectedRows(string $query): int
    {
        $affected = $this->database->query($query);

        return $affected === false ? 0 : $affected;
    }
}
