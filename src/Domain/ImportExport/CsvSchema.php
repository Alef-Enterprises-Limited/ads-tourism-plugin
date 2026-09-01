<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;

final readonly class CsvSchema
{
    public const CLEAR_VALUE = '__CLEAR__';

    public const GALLERY_DELIMITER = '|';

    public const SCHEMA_VERSION = '1.0';

    public function __construct(private RecordFieldSchema $fields) {}

    /**
     * @return array<string, string>
     */
    public function columns(ContentType $contentType, bool $includeTaxonomies = true): array
    {
        $columns = [
            'external_id' => 'External ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'excerpt' => 'Excerpt',
            'parent_external_id' => 'Parent external ID',
            'featured_attachment_id' => 'Featured attachment ID',
            'featured_media_url' => 'Featured media URL',
            'featured_media_url_type' => 'Featured media URL type',
            'gallery_urls' => 'Gallery URLs',
        ];

        foreach ($this->fields->for($contentType) as $field) {
            if (
                $field->key === 'ads_tourism_external_id'
                || $field->key === 'ads_tourism_verification_status'
                || !$field->editable
                || $field->administratorsOnly
            ) {
                continue;
            }

            $columns[$field->key] = $field->label;
        }

        if (!$includeTaxonomies) {
            return $columns;
        }

        foreach ($this->taxonomiesFor($contentType) as $taxonomy) {
            $columns[$this->taxonomyColumn($taxonomy)] = $this->taxonomyLabel($taxonomy);
        }

        return $columns;
    }

    /**
     * @return list<string>
     */
    public function headers(ContentType $contentType, bool $includeTaxonomies = true): array
    {
        return array_keys($this->columns($contentType, $includeTaxonomies));
    }

    public function field(ContentType $contentType, string $column): ?FieldDefinition
    {
        if (!str_starts_with($column, 'ads_tourism_')) {
            return null;
        }

        return $this->fields->find($contentType, $column);
    }

    public function isTaxonomyColumn(string $column): bool
    {
        return str_starts_with($column, 'taxonomy_');
    }

    public function taxonomyFromColumn(ContentType $contentType, string $column): ?TourismTaxonomy
    {
        if (!$this->isTaxonomyColumn($column)) {
            return null;
        }

        $taxonomy = TourismTaxonomy::tryFrom(substr($column, strlen('taxonomy_')));

        return $taxonomy !== null && in_array($contentType->value, $taxonomy->objectTypes(), true)
            ? $taxonomy
            : null;
    }

    /**
     * @return list<TourismTaxonomy>
     */
    public function taxonomiesFor(ContentType $contentType): array
    {
        return array_values(array_filter(
            TourismTaxonomy::cases(),
            static fn(TourismTaxonomy $taxonomy): bool => in_array(
                $contentType->value,
                $taxonomy->objectTypes(),
                true,
            ),
        ));
    }

    public function taxonomyColumn(TourismTaxonomy $taxonomy): string
    {
        return 'taxonomy_' . $taxonomy->value;
    }

    public function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', trim($header)) ?? '';
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9_]+/', '_', $header) ?? '';

        return trim($header, '_');
    }

    private function taxonomyLabel(TourismTaxonomy $taxonomy): string
    {
        return ucwords(str_replace('_', ' ', substr($taxonomy->value, strlen('ads_')))) . ' slugs';
    }
}
