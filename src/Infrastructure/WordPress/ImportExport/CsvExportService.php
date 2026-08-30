<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ExportBundle;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ExportRequest;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use PclZip;
use RuntimeException;
use stdClass;
use WP_Post;
use WP_Term;
use wpdb;
use ZipArchive;

final readonly class CsvExportService
{
    public function __construct(
        private wpdb $database,
        private CsvSchema $schema,
        private CsvSecurity $security,
        private MediaLinkRepository $mediaLinks,
    ) {}

    public function createBundle(ExportRequest $request): ExportBundle
    {
        $directory = $this->createDirectory('export');
        $posts = $this->findPosts($request);
        $grouped = [];

        foreach ($posts as $post) {
            $contentType = ContentType::tryFrom($post->post_type);

            if ($contentType !== null) {
                $grouped[$contentType->value][] = $post;
            }
        }

        $files = [];

        foreach (ContentType::cases() as $contentType) {
            if ($request->contentType !== null && $request->contentType !== $contentType) {
                continue;
            }

            $filename = $this->recordFilename($contentType);
            $path = $directory . '/' . $filename;
            $this->writeRecords($path, $contentType, $grouped[$contentType->value] ?? []);
            $files[$filename] = count($grouped[$contentType->value] ?? []);
        }

        $postIds = array_map(static fn(WP_Post $post): int => $post->ID, $posts);
        $this->writeTaxonomies($directory . '/taxonomies.csv');
        $files['taxonomies.csv'] = count(TourismTaxonomy::cases());
        $files['relationships.csv'] = $this->writeRelationships($directory . '/relationships.csv', $postIds);
        $files['media.csv'] = $this->writeMedia($directory . '/media.csv', $posts);
        $this->writeManifest($directory . '/manifest.json', $request, $files, count($posts), $directory);
        $files['manifest.json'] = 1;

        $filename = 'ads-tourism-export-' . gmdate('Ymd-His') . '.zip';
        $zipPath = dirname($directory) . '/' . $filename;
        $this->zip($directory, $zipPath, array_keys($files));
        $this->deleteDirectory($directory);

        return new ExportBundle($zipPath, $filename, count($posts));
    }

    /** @return list<WP_Post> */
    private function findPosts(ExportRequest $request): array
    {
        $arguments = [
            'post_type' => $request->contentType?->value ?? array_map(
                static fn(ContentType $contentType): string => $contentType->value,
                ContentType::cases(),
            ),
            'post_status' => $request->postStatus !== '' ? $request->postStatus : 'any',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'suppress_filters' => false,
        ];

        if ($request->selectedPostIds !== []) {
            $arguments['post__in'] = $request->selectedPostIds;
        }

        if ($request->verificationStatus !== '') {
            $arguments['meta_key'] = 'ads_tourism_verification_status';
            $arguments['meta_value'] = $request->verificationStatus;
        }

        $dateQuery = [];

        if ($request->modifiedAfter !== '') {
            $dateQuery['after'] = $request->modifiedAfter;
        }

        if ($request->modifiedBefore !== '') {
            $dateQuery['before'] = $request->modifiedBefore;
        }

        if ($dateQuery !== []) {
            $dateQuery['column'] = 'post_modified_gmt';
            $dateQuery['inclusive'] = true;
            $arguments['date_query'] = [$dateQuery];
        }

        return array_values(array_filter(
            get_posts($arguments),
            static fn(mixed $post): bool => $post instanceof WP_Post,
        ));
    }

    /** @param list<WP_Post> $posts */
    private function writeRecords(string $path, ContentType $contentType, array $posts): void
    {
        $headers = $this->schema->headers($contentType);
        $handle = $this->openCsv($path);
        $this->putRow($handle, $headers);

        foreach ($posts as $post) {
            $values = [];

            foreach ($headers as $header) {
                $values[] = $this->recordValue($post, $contentType, $header);
            }

            $this->putRow($handle, $values);
        }

        fclose($handle);
    }

    private function recordValue(WP_Post $post, ContentType $contentType, string $column): string
    {
        return match ($column) {
            'external_id' => (string) get_post_meta($post->ID, 'ads_tourism_external_id', true),
            'title' => $post->post_title,
            'slug' => $post->post_name,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'parent_external_id' => $post->post_parent > 0
                ? (string) get_post_meta($post->post_parent, 'ads_tourism_external_id', true)
                : '',
            'featured_attachment_id' => (string) (get_post_thumbnail_id($post->ID) ?: ''),
            'featured_media_url' => (string) get_post_meta(
                $post->ID,
                'ads_tourism_external_featured_media_url',
                true,
            ),
            'featured_media_url_type' => (string) get_post_meta(
                $post->ID,
                'ads_tourism_external_featured_media_url_type',
                true,
            ),
            'gallery_urls' => $this->galleryUrls($post->ID),
            default => $this->schema->isTaxonomyColumn($column)
                ? $this->taxonomySlugs($post->ID, $contentType, $column)
                : $this->stringify(get_post_meta($post->ID, $column, true)),
        };
    }

    private function galleryUrls(int $postId): string
    {
        $urls = [];

        foreach ($this->mediaLinks->findForEntity($postId, MediaRole::GALLERY) as $link) {
            if ($link->mediaUrl !== null) {
                $urls[] = $link->mediaUrl;
            }
        }

        return implode(CsvSchema::GALLERY_DELIMITER, $urls);
    }

    private function taxonomySlugs(int $postId, ContentType $contentType, string $column): string
    {
        $taxonomy = $this->schema->taxonomyFromColumn($contentType, $column);

        if ($taxonomy === null) {
            return '';
        }

        $slugs = wp_get_object_terms($postId, $taxonomy->value, ['fields' => 'slugs']);

        if (is_wp_error($slugs) || !is_array($slugs)) {
            return '';
        }

        return implode(CsvSchema::GALLERY_DELIMITER, array_map('strval', $slugs));
    }

    private function writeTaxonomies(string $path): void
    {
        $handle = $this->openCsv($path);
        $this->putRow($handle, ['taxonomy', 'slug', 'name', 'parent_slug', 'description']);

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            $terms = get_terms(['taxonomy' => $taxonomy->value, 'hide_empty' => false]);

            if (is_wp_error($terms) || !is_array($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if (!$term instanceof WP_Term) {
                    continue;
                }

                $parentSlug = '';

                if ($term->parent > 0) {
                    $parent = get_term($term->parent, $taxonomy->value);
                    $parentSlug = $parent instanceof WP_Term ? $parent->slug : '';
                }

                $this->putRow($handle, [
                    $taxonomy->value,
                    $term->slug,
                    $term->name,
                    $parentSlug,
                    $term->description,
                ]);
            }
        }

        fclose($handle);
    }

    /** @param list<int> $postIds */
    private function writeRelationships(string $path, array $postIds): int
    {
        $handle = $this->openCsv($path);
        $this->putRow($handle, [
            'source_external_id',
            'source_record_type',
            'relation_key',
            'target_external_id',
            'target_record_type',
            'is_primary',
            'sort_order',
            'metadata_json',
        ]);

        if ($postIds === []) {
            fclose($handle);

            return 0;
        }

        $placeholders = implode(', ', array_fill(0, count($postIds), '%d'));
        $query = $this->database->prepare(
            "SELECT * FROM {$this->database->prefix}ads_tourism_relations
            WHERE source_post_id IN ({$placeholders}) OR target_post_id IN ({$placeholders})
            ORDER BY id ASC",
            ...[...$postIds, ...$postIds],
        );
        $rows = $this->database->get_results($query);
        $count = 0;

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!$row instanceof stdClass || RelationType::tryFrom((string) ($row->relation_key ?? '')) === null) {
                    continue;
                }

                $sourceId = (int) ($row->source_post_id ?? 0);
                $targetId = (int) ($row->target_post_id ?? 0);
                $this->putRow($handle, [
                    (string) get_post_meta($sourceId, 'ads_tourism_external_id', true),
                    (string) get_post_type($sourceId),
                    (string) ($row->relation_key ?? ''),
                    (string) get_post_meta($targetId, 'ads_tourism_external_id', true),
                    (string) get_post_type($targetId),
                    (string) ((int) ($row->is_primary ?? 0)),
                    (string) ((int) ($row->sort_order ?? 0)),
                    (string) ($row->metadata_json ?? ''),
                ]);
                ++$count;
            }
        }

        fclose($handle);

        return $count;
    }

    /** @param list<WP_Post> $posts */
    private function writeMedia(string $path, array $posts): int
    {
        $handle = $this->openCsv($path);
        $this->putRow($handle, [
            'entity_external_id',
            'record_type',
            'attachment_id',
            'media_url',
            'url_type',
            'media_role',
            'custom_title',
            'custom_alt_text',
            'custom_caption',
            'credit',
            'rights_notice',
            'is_primary',
            'sort_order',
        ]);
        $count = 0;

        foreach ($posts as $post) {
            $externalId = (string) get_post_meta($post->ID, 'ads_tourism_external_id', true);

            foreach ($this->mediaLinks->findForEntity($post->ID) as $link) {
                $this->writeMediaRow($handle, $post, $externalId, $link);
                ++$count;
            }
        }

        fclose($handle);

        return $count;
    }

    /** @param resource $handle */
    private function writeMediaRow($handle, WP_Post $post, string $externalId, MediaLink $link): void
    {
        $this->putRow($handle, [
            $externalId,
            $post->post_type,
            $link->attachmentId === null ? '' : (string) $link->attachmentId,
            $link->mediaUrl ?? '',
            $link->urlType?->value ?? '',
            $link->role->value,
            $link->customTitle,
            $link->customAltText,
            $link->customCaption,
            $link->credit,
            $link->rightsNotice,
            $link->isPrimary ? '1' : '0',
            (string) $link->sortOrder,
        ]);
    }

    /**
     * @param array<string, int> $files
     */
    private function writeManifest(
        string $path,
        ExportRequest $request,
        array $files,
        int $recordCount,
        string $directory,
    ): void {
        $checksums = [];

        foreach (array_keys($files) as $file) {
            $checksums[$file] = hash_file('sha256', $directory . '/' . $file) ?: '';
        }

        $manifest = [
            'schema_version' => CsvSchema::SCHEMA_VERSION,
            'plugin_version' => \AlefDigitalSolutions\ADSTourism\Plugin::VERSION,
            'exported_at_utc' => gmdate(DATE_ATOM),
            'site_url' => home_url('/'),
            'record_count' => $recordCount,
            'filters' => [
                'record_type' => $request->contentType?->value,
                'post_status' => $request->postStatus,
                'verification_status' => $request->verificationStatus,
                'modified_after' => $request->modifiedAfter,
                'modified_before' => $request->modifiedBefore,
                'selected_post_ids' => $request->selectedPostIds,
            ],
            'files' => $files,
            'sha256' => $checksums,
        ];
        $encoded = wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (!is_string($encoded) || file_put_contents($path, $encoded . "\n") === false) {
            throw new RuntimeException('The export manifest could not be written.');
        }
    }

    /** @return resource */
    private function openCsv(string $path)
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('An export CSV could not be created.');
        }

        fwrite($handle, "\xEF\xBB\xBF");

        return $handle;
    }

    /**
     * @param resource     $handle
     * @param list<string> $values
     */
    private function putRow($handle, array $values): void
    {
        fputcsv($handle, array_map($this->security->escapeForSpreadsheet(...), $values), ',', '"', '');
    }

    private function stringify(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            $encoded = wp_json_encode($value, JSON_UNESCAPED_SLASHES);

            return is_string($encoded) ? $encoded : '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return is_scalar($value) ? (string) $value : '';
    }

    private function createDirectory(string $purpose): string
    {
        $uploads = wp_upload_dir();
        $baseDirectory = rtrim((string) ($uploads['basedir'] ?? ''), '/') . '/ads-tourism-transfers';

        if ($baseDirectory === '/ads-tourism-transfers' || (!is_dir($baseDirectory) && !wp_mkdir_p($baseDirectory))) {
            throw new RuntimeException('The secure transfer directory could not be created.');
        }

        file_put_contents($baseDirectory . '/index.php', "<?php\n// Silence is golden.\n");
        file_put_contents($baseDirectory . '/.htaccess', "Require all denied\n");
        $directory = $baseDirectory . '/' . $purpose . '-' . wp_generate_uuid4();

        if (!wp_mkdir_p($directory)) {
            throw new RuntimeException('The temporary export directory could not be created.');
        }

        return $directory;
    }

    /** @param list<string> $files */
    private function zip(string $directory, string $zipPath, array $files): void
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('The export ZIP could not be created.');
            }

            foreach ($files as $file) {
                $zip->addFile($directory . '/' . $file, $file);
            }

            $zip->close();

            return;
        }

        if (!class_exists(PclZip::class)) {
            // @phpstan-ignore-next-line WordPress supplies this file at runtime.
            require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }

        $archive = new PclZip($zipPath);
        $created = $archive->create(
            array_map(static fn(string $file): string => $directory . '/' . $file, $files),
            PCLZIP_OPT_REMOVE_PATH,
            $directory,
        );

        if ($created === 0) {
            throw new RuntimeException('The export ZIP could not be created.');
        }
    }

    private function deleteDirectory(string $directory): void
    {
        foreach (glob($directory . '/*') ?: [] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    private function recordFilename(ContentType $contentType): string
    {
        return match ($contentType) {
            ContentType::PLACE => 'places.csv',
            ContentType::ACTIVITY => 'activities.csv',
            ContentType::STAY => 'stays.csv',
            ContentType::OPERATOR => 'operators.csv',
            ContentType::PACKAGE => 'packages.csv',
        };
    }
}
