<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;

final readonly class MetadataRegistrar
{
    public function __construct(
        private RecordFieldSchema $schema,
        private MetaValueSanitizer $sanitizer,
    ) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            foreach ($this->schema->for($contentType) as $field) {
                $this->registerField($contentType, $field);
            }

            register_post_meta($contentType->value, VerificationHistoryService::HISTORY_KEY, [
                'type' => 'array',
                'single' => true,
                'show_in_rest' => false,
                'auth_callback' => static fn(): bool => current_user_can('manage_options'),
            ]);
        }
    }

    private function registerField(ContentType $contentType, FieldDefinition $field): void
    {
        $arguments = [
            'type' => $field->type->restType(),
            'single' => true,
            'show_in_rest' => $field->administratorsOnly
                ? false
                : ['schema' => $field->restSchema()],
            'sanitize_callback' => fn(mixed $value): mixed => $this->sanitizer->sanitize($field, $value),
            'auth_callback' => static function (
                bool $allowed,
                string $metaKey,
                int $postId,
            ) use ($field): bool {
                if ($field->administratorsOnly) {
                    return current_user_can('manage_options');
                }

                return current_user_can('edit_post', $postId);
            },
        ];

        if ($field->default !== null) {
            $arguments['default'] = $field->default;
        }

        register_post_meta($contentType->value, $field->key, $arguments);
    }
}
