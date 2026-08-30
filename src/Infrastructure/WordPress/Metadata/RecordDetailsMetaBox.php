<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use WP_Post;

final readonly class RecordDetailsMetaBox
{
    private const NONCE_ACTION = 'ads_tourism_save_record_details';

    private const NONCE_NAME = 'ads_tourism_details_nonce';

    public function __construct(
        private RecordFieldSchema $schema,
        private MetaValueSanitizer $sanitizer,
    ) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_meta_box(
                'ads-tourism-record-details',
                __('Tourism details', 'ads-tourism'),
                [$this, 'render'],
                $contentType->value,
                'normal',
                'default',
            );
        }
    }

    public function render(WP_Post $post): void
    {
        $contentType = ContentType::tryFrom($post->post_type);

        if ($contentType === null) {
            return;
        }

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

        echo '<table class="form-table" role="presentation"><tbody>';

        foreach ($this->schema->for($contentType) as $field) {
            if ($field->administratorsOnly && !current_user_can('manage_options')) {
                continue;
            }

            $this->renderField($post->ID, $field);
        }

        echo '</tbody></table>';
    }

    public function save(int $postId): void
    {
        if (!$this->requestCanSave($postId)) {
            return;
        }

        $contentType = ContentType::tryFrom((string) get_post_type($postId));
        $submittedFields = isset($_POST['ads_tourism_fields']) && is_array($_POST['ads_tourism_fields'])
            ? wp_unslash($_POST['ads_tourism_fields'])
            : [];

        if ($contentType === null) {
            return;
        }

        foreach ($this->schema->for($contentType) as $field) {
            if (!$field->editable || !array_key_exists($field->key, $submittedFields)) {
                continue;
            }

            if ($field->administratorsOnly && !current_user_can('manage_options')) {
                continue;
            }

            $value = $this->sanitizer->sanitize($field, $submittedFields[$field->key]);

            if ($value === '' || $value === null || $value === []) {
                delete_post_meta($postId, $field->key);
                continue;
            }

            update_post_meta($postId, $field->key, $value);
        }
    }

    private function requestCanSave(int $postId): bool
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (wp_is_post_revision($postId) !== false) {
            return false;
        }

        $nonce = isset($_POST[self::NONCE_NAME])
            ? sanitize_text_field((string) wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        return $nonce !== ''
            && wp_verify_nonce($nonce, self::NONCE_ACTION) !== false
            && current_user_can('edit_post', $postId);
    }

    private function renderField(int $postId, FieldDefinition $field): void
    {
        $value = get_post_meta($postId, $field->key, true);

        if ($value === '' && $field->default !== null) {
            $value = $field->default;
        }

        echo '<tr>';
        echo '<th scope="row"><label for="' . esc_attr($field->key) . '">';
        echo esc_html($field->label);
        echo '</label></th><td>';

        $this->renderInput($field, $value);

        if ($field->description !== '') {
            echo '<p class="description">' . esc_html($field->description) . '</p>';
        }

        echo '</td></tr>';
    }

    private function renderInput(FieldDefinition $field, mixed $value): void
    {
        $name = 'ads_tourism_fields[' . $field->key . ']';
        $readonly = $field->editable ? '' : ' readonly';

        if ($field->type === FieldType::TEXTAREA) {
            echo '<textarea class="large-text" rows="4" id="' . esc_attr($field->key) . '" name="';
            echo esc_attr($name) . '"' . $readonly . '>' . esc_textarea((string) $value) . '</textarea>';

            return;
        }

        if ($field->type === FieldType::OBJECT || $field->type === FieldType::ARRAY) {
            $json = is_array($value) ? wp_json_encode($value, JSON_PRETTY_PRINT) : (string) $value;
            echo '<textarea class="large-text code" rows="6" id="' . esc_attr($field->key) . '" name="';
            echo esc_attr($name) . '"' . $readonly . '>' . esc_textarea((string) $json) . '</textarea>';

            return;
        }

        if ($field->type === FieldType::SELECT) {
            echo '<select id="' . esc_attr($field->key) . '" name="' . esc_attr($name) . '"' . $readonly . '>';

            foreach ($field->options as $optionValue => $optionLabel) {
                echo '<option value="' . esc_attr($optionValue) . '" ';
                echo selected((string) $value, $optionValue, false) . '>' . esc_html($optionLabel) . '</option>';
            }

            echo '</select>';

            return;
        }

        if ($field->type === FieldType::BOOLEAN) {
            echo '<input type="hidden" name="' . esc_attr($name) . '" value="0">';
            echo '<input type="checkbox" id="' . esc_attr($field->key) . '" name="' . esc_attr($name) . '" value="1" ';
            echo checked((bool) $value, true, false) . $readonly . '>';

            return;
        }

        $inputType = match ($field->type) {
            FieldType::EMAIL => 'email',
            FieldType::URL => 'url',
            FieldType::DATE => 'date',
            FieldType::DATETIME => 'datetime-local',
            FieldType::INTEGER,
            FieldType::NUMBER => 'number',
            default => 'text',
        };
        $step = $field->type === FieldType::NUMBER ? ' step="any"' : '';

        echo '<input class="regular-text" type="' . esc_attr($inputType) . '" id="' . esc_attr($field->key) . '"';
        echo ' name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '"' . $step . $readonly . '>';
    }
}
