<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use WP_Term;

final class TaxonomyColorManager
{
    public const META_KEY = 'ads_tourism_term_color';

    private const FIELD_NAME = 'ads_tourism_term_color';

    private const NONCE_ACTION = 'ads_tourism_save_term_color';

    private const NONCE_NAME = 'ads_tourism_term_color_nonce';

    public function register(): void
    {
        foreach (TourismTaxonomy::cases() as $taxonomy) {
            add_action($taxonomy->value . '_add_form_fields', [$this, 'renderAddField']);
            add_action($taxonomy->value . '_edit_form_fields', [$this, 'renderEditField'], 10, 2);
        }

        add_action('created_term', [$this, 'save'], 10, 3);
        add_action('edited_term', [$this, 'save'], 10, 3);
    }

    public function registerMeta(): void
    {
        foreach (TourismTaxonomy::cases() as $taxonomy) {
            register_term_meta($taxonomy->value, self::META_KEY, [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
                'sanitize_callback' => [$this, 'sanitizeColor'],
                'auth_callback' => static fn(): bool => current_user_can('manage_categories'),
            ]);
        }
    }

    public function enqueueAssets(): void
    {
        $taxonomy = isset($_GET['taxonomy']) && is_string($_GET['taxonomy'])
            ? sanitize_key((string) wp_unslash($_GET['taxonomy']))
            : '';

        if (TourismTaxonomy::tryFrom($taxonomy) === null) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_add_inline_script(
            'wp-color-picker',
            "jQuery(function($){ $('.ads-tourism-term-color').wpColorPicker(); });",
        );
    }

    public function renderAddField(): void
    {
        echo '<div class="form-field term-color-wrap"><label for="' . esc_attr(self::FIELD_NAME) . '">'
            . esc_html__('Color (RGB hex)', 'ads-tourism') . '</label>';
        $this->renderInput('');
        echo '<p class="description">' . esc_html__(
            'Optional RGB hex color for this term. Leave blank for no color.',
            'ads-tourism',
        ) . '</p>' . wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, false, false) . '</div>';
    }

    public function renderEditField(WP_Term $term, string $taxonomy): void
    {
        if (TourismTaxonomy::tryFrom($taxonomy) === null) {
            return;
        }

        $value = get_term_meta($term->term_id, self::META_KEY, true);
        $value = is_scalar($value) ? $this->sanitizeColor((string) $value) : '';

        echo '<tr class="form-field term-color-wrap"><th scope="row"><label for="'
            . esc_attr(self::FIELD_NAME) . '">' . esc_html__('Color (RGB hex)', 'ads-tourism')
            . '</label></th><td>';
        $this->renderInput($value);
        echo '<p class="description">' . esc_html__(
            'Optional RGB hex color for this term. Clear the field for no color.',
            'ads-tourism',
        ) . '</p>' . wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME, false, false) . '</td></tr>';
    }

    public function save(int $termId, int $termTaxonomyId, string $taxonomy): void
    {
        if (
            TourismTaxonomy::tryFrom($taxonomy) === null
            || !current_user_can('manage_categories')
            || !isset($_POST[self::FIELD_NAME])
        ) {
            return;
        }

        $nonce = isset($_POST[self::NONCE_NAME])
            ? sanitize_text_field((string) wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        if ($nonce === '' || wp_verify_nonce($nonce, self::NONCE_ACTION) === false) {
            return;
        }

        $color = $this->sanitizeColor(wp_unslash($_POST[self::FIELD_NAME]));

        if ($color === '') {
            delete_term_meta($termId, self::META_KEY);

            return;
        }

        update_term_meta($termId, self::META_KEY, $color);
    }

    public function sanitizeColor(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $color = sanitize_hex_color(trim((string) $value));

        return is_string($color) ? $color : '';
    }

    private function renderInput(string $value): void
    {
        echo '<input type="text" class="ads-tourism-term-color" id="' . esc_attr(self::FIELD_NAME)
            . '" name="' . esc_attr(self::FIELD_NAME) . '" value="' . esc_attr($value)
            . '" placeholder="#RRGGBB" maxlength="7" data-default-color="">';
    }
}
