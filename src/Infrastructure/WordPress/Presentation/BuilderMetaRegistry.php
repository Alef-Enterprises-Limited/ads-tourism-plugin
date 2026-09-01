<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;

final readonly class BuilderMetaRegistry
{
    public function __construct(private RecordFieldSchema $schema) {}

    public function register(): void
    {
        add_filter('ads_tourism_builder_meta_keys', [$this, 'filterKeys'], 10, 2);
    }

    /**
     * Returns the registered scalar metadata that builders may expose as dynamic content.
     * Complex arrays and private editorial fields deliberately remain outside this contract.
     *
     * @param mixed $keys
     * @param mixed $postType
     *
     * @return list<string>
     */
    public function filterKeys(mixed $keys, mixed $postType): array
    {
        $contentType = is_string($postType) ? ContentType::tryFrom($postType) : null;
        $registered = is_array($keys) ? array_values(array_filter($keys, 'is_string')) : [];

        if ($contentType === null) {
            return $registered;
        }

        foreach ($this->schema->for($contentType) as $field) {
            if (
                !$field->administratorsOnly
                && $field->type !== FieldType::ARRAY
                && $field->type !== FieldType::OBJECT
            ) {
                $registered[] = $field->key;
            }
        }

        return array_values(array_unique($registered));
    }
}
