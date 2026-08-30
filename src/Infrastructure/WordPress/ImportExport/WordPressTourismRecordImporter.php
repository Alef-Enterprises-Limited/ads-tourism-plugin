<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Application\Media\MediaLinkService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRecordResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TourismRecordImporter;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetaValueSanitizer;
use Throwable;
use WP_Error;
use WP_Post;
use WP_Term;
use wpdb;

final readonly class WordPressTourismRecordImporter implements TourismRecordImporter
{
    public function __construct(
        private wpdb $database,
        private CsvSchema $csvSchema,
        private RecordFieldSchema $fieldSchema,
        private MetaValueSanitizer $sanitizer,
        private MediaLinkService $mediaLinks,
    ) {}

    public function import(
        ContentType $contentType,
        array $values,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
    ): ImportRecordResult {
        $externalId = $values['external_id'] ?? '';
        $existingId = $this->findByExternalId($contentType, $externalId);

        if ($existingId !== null && $duplicatePolicy === DuplicatePolicy::SKIP) {
            return new ImportRecordResult(ImportRecordResult::SKIPPED, $existingId);
        }

        $updating = $existingId !== null && $duplicatePolicy === DuplicatePolicy::UPDATE;

        if ($existingId !== null && $duplicatePolicy === DuplicatePolicy::CREATE_NEW) {
            $externalId = $this->uniqueExternalId($contentType, $externalId);
        }

        $errors = $this->preflight($contentType, $values, $taxonomyMode, $allowTermCreation);

        if ($errors !== []) {
            return new ImportRecordResult(ImportRecordResult::REJECTED, null, $errors);
        }

        $this->database->query('START TRANSACTION');

        try {
            $postId = $this->writePost($contentType, $values, $updating ? $existingId : null);
            update_post_meta($postId, 'ads_tourism_external_id', sanitize_text_field($externalId));

            if (!$updating) {
                update_post_meta($postId, 'ads_tourism_verification_status', 'unverified');
            }

            $this->writeFields($postId, $contentType, $values, $updating);
            $this->writeFeaturedMedia($postId, $values, $updating);
            $this->writeGallery($postId, $values, $updating);

            if ($taxonomyMode === TaxonomyImportMode::ADVANCED) {
                $this->writeTaxonomies($postId, $contentType, $values, $allowTermCreation, $updating);
            }

            $this->database->query('COMMIT');

            return new ImportRecordResult(
                $updating ? ImportRecordResult::UPDATED : ImportRecordResult::CREATED,
                $postId,
            );
        } catch (Throwable $exception) {
            $this->database->query('ROLLBACK');

            return new ImportRecordResult(ImportRecordResult::REJECTED, null, [$exception->getMessage()]);
        }
    }

    /**
     * @param array<string, string> $values
     *
     * @return list<string>
     */
    private function preflight(
        ContentType $contentType,
        array $values,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
    ): array {
        $errors = [];
        $attachmentId = $values['featured_attachment_id'] ?? '';

        if (
            $attachmentId !== ''
            && $attachmentId !== CsvSchema::CLEAR_VALUE
            && !$this->isImageAttachment((int) $attachmentId)
        ) {
            $errors[] = 'The featured attachment does not exist or is not an image.';
        }

        $parentExternalId = $values['parent_external_id'] ?? '';

        if (
            $parentExternalId !== ''
            && $parentExternalId !== CsvSchema::CLEAR_VALUE
            && $this->findByExternalId($contentType, $parentExternalId) === null
        ) {
            $errors[] = 'The parent_external_id does not match an existing record of the same type.';
        }

        if ($taxonomyMode !== TaxonomyImportMode::ADVANCED) {
            return $errors;
        }

        foreach ($values as $column => $value) {
            $taxonomy = $this->csvSchema->taxonomyFromColumn($contentType, $column);

            if ($taxonomy === null || $value === '' || $value === CsvSchema::CLEAR_VALUE || $allowTermCreation) {
                continue;
            }

            foreach ($this->split($value) as $slug) {
                if (get_term_by('slug', $slug, $taxonomy->value) === false) {
                    $errors[] = sprintf('Unknown %s term slug: %s.', $taxonomy->value, $slug);
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<string, string> $values
     */
    private function writePost(ContentType $contentType, array $values, ?int $existingId): int
    {
        $post = $existingId === null
            ? [
                'post_type' => $contentType->value,
                'post_status' => 'draft',
                'post_title' => sanitize_text_field($values['title'] ?? ''),
            ]
            : ['ID' => $existingId];

        foreach (['title' => 'post_title', 'slug' => 'post_name'] as $column => $postKey) {
            $this->assignPostValue($post, $postKey, $values[$column] ?? '', $existingId !== null, false);
        }

        foreach (['content' => 'post_content', 'excerpt' => 'post_excerpt'] as $column => $postKey) {
            $this->assignPostValue($post, $postKey, $values[$column] ?? '', $existingId !== null, true);
        }

        $parent = $values['parent_external_id'] ?? '';

        if ($parent === CsvSchema::CLEAR_VALUE) {
            $post['post_parent'] = 0;
        } elseif ($parent !== '') {
            $post['post_parent'] = $this->findByExternalId($contentType, $parent) ?? 0;
        }

        $postId = wp_insert_post($post, true);

        if ($postId instanceof WP_Error) {
            throw new \RuntimeException($postId->get_error_message());
        }

        return (int) $postId;
    }

    /**
     * @param array<string, mixed> $post
     */
    private function assignPostValue(
        array &$post,
        string $postKey,
        string $value,
        bool $updating,
        bool $multiline,
    ): void {
        if ($value === '' && $updating) {
            return;
        }

        if ($value === CsvSchema::CLEAR_VALUE) {
            $post[$postKey] = '';

            return;
        }

        if ($value !== '') {
            $post[$postKey] = $multiline ? sanitize_textarea_field($value) : sanitize_text_field($value);
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function writeFields(int $postId, ContentType $contentType, array $values, bool $updating): void
    {
        foreach ($this->fieldSchema->for($contentType) as $field) {
            if (
                !$field->editable
                || $field->administratorsOnly
                || in_array($field->key, ['ads_tourism_external_id', 'ads_tourism_verification_status'], true)
            ) {
                continue;
            }

            $value = $values[$field->key] ?? '';

            if ($value === '' && $updating) {
                continue;
            }

            if ($value === CsvSchema::CLEAR_VALUE) {
                delete_post_meta($postId, $field->key);
            } elseif ($value !== '') {
                update_post_meta($postId, $field->key, $this->sanitizer->sanitize($field, $value));
            }
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function writeFeaturedMedia(int $postId, array $values, bool $updating): void
    {
        $attachmentId = $values['featured_attachment_id'] ?? '';
        $mediaUrl = $values['featured_media_url'] ?? '';
        $urlType = $values['featured_media_url_type'] ?? '';

        if ($attachmentId === CsvSchema::CLEAR_VALUE || $mediaUrl === CsvSchema::CLEAR_VALUE) {
            delete_post_thumbnail($postId);
            delete_post_meta($postId, 'ads_tourism_external_featured_media_url');
            delete_post_meta($postId, 'ads_tourism_external_featured_media_url_type');
        }

        if ($attachmentId !== '' && $attachmentId !== CsvSchema::CLEAR_VALUE) {
            set_post_thumbnail($postId, (int) $attachmentId);
            delete_post_meta($postId, 'ads_tourism_external_featured_media_url');
            delete_post_meta($postId, 'ads_tourism_external_featured_media_url_type');

            return;
        }

        if ($mediaUrl !== '' && $mediaUrl !== CsvSchema::CLEAR_VALUE) {
            update_post_meta($postId, 'ads_tourism_external_featured_media_url', esc_url_raw($mediaUrl, ['https']));
            update_post_meta(
                $postId,
                'ads_tourism_external_featured_media_url_type',
                $urlType === 'relative' ? 'relative' : 'absolute',
            );
            delete_post_thumbnail($postId);
        } elseif (!$updating && $attachmentId === '') {
            delete_post_thumbnail($postId);
        }
    }

    /**
     * @param array<string, string> $values
     */
    private function writeGallery(int $postId, array $values, bool $updating): void
    {
        $gallery = $values['gallery_urls'] ?? '';

        if ($gallery === '' && $updating) {
            return;
        }

        $links = array_values(array_filter(
            $this->mediaLinks->find($postId),
            static fn(MediaLink $link): bool => $link->role !== MediaRole::GALLERY,
        ));

        if ($gallery !== '' && $gallery !== CsvSchema::CLEAR_VALUE) {
            foreach ($this->split($gallery) as $url) {
                $links[] = new MediaLink(
                    $postId,
                    null,
                    $url,
                    str_starts_with($url, '/') ? MediaUrlType::RELATIVE : MediaUrlType::ABSOLUTE,
                    MediaRole::GALLERY,
                );
            }
        }

        $this->mediaLinks->replace($postId, $links);
    }

    /**
     * @param array<string, string> $values
     */
    private function writeTaxonomies(
        int $postId,
        ContentType $contentType,
        array $values,
        bool $allowTermCreation,
        bool $updating,
    ): void {
        foreach ($values as $column => $value) {
            $taxonomy = $this->csvSchema->taxonomyFromColumn($contentType, $column);

            if ($taxonomy === null || ($value === '' && $updating)) {
                continue;
            }

            if ($value === CsvSchema::CLEAR_VALUE || $value === '') {
                wp_set_object_terms($postId, [], $taxonomy->value, false);
                continue;
            }

            $termIds = [];

            foreach ($this->split($value) as $slug) {
                $term = get_term_by('slug', $slug, $taxonomy->value);

                if ($term instanceof WP_Term) {
                    $termIds[] = $term->term_id;
                    continue;
                }

                if (!$allowTermCreation) {
                    continue;
                }

                $created = wp_insert_term(ucwords(str_replace('-', ' ', $slug)), $taxonomy->value, ['slug' => $slug]);

                if ($created instanceof WP_Error) {
                    throw new \RuntimeException($created->get_error_message());
                }

                $termIds[] = (int) ($created['term_id'] ?? 0);
            }

            $result = wp_set_object_terms($postId, $termIds, $taxonomy->value, false);

            if ($result instanceof WP_Error) {
                throw new \RuntimeException($result->get_error_message());
            }
        }
    }

    private function findByExternalId(ContentType $contentType, string $externalId): ?int
    {
        if ($externalId === '') {
            return null;
        }

        $posts = get_posts([
            'post_type' => $contentType->value,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => 'ads_tourism_external_id',
            'meta_value' => $externalId,
            'no_found_rows' => true,
            'suppress_filters' => true,
        ]);
        $postId = $posts[0] ?? null;

        return $postId instanceof WP_Post ? $postId->ID : (is_int($postId) ? $postId : null);
    }

    private function uniqueExternalId(ContentType $contentType, string $externalId): string
    {
        do {
            $candidate = substr($externalId, 0, 180) . '-' . strtolower(wp_generate_password(8, false, false));
        } while ($this->findByExternalId($contentType, $candidate) !== null);

        return $candidate;
    }

    private function isImageAttachment(int $attachmentId): bool
    {
        return $attachmentId > 0
            && get_post_type($attachmentId) === 'attachment'
            && wp_attachment_is_image($attachmentId);
    }

    /** @return list<string> */
    private function split(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(CsvSchema::GALLERY_DELIMITER, $value))));
    }
}
