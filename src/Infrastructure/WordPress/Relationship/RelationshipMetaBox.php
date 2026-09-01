<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship;

use AlefDigitalSolutions\ADSTourism\Application\Relationship\RelationshipService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\Relationship;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipSide;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use InvalidArgumentException;
use WP_Post;

final readonly class RelationshipMetaBox
{
    private const NONCE_ACTION = 'ads_tourism_save_relationships';

    private const NONCE_NAME = 'ads_tourism_relationships_nonce';

    public function __construct(
        private RelationshipService $relationships,
        private string $pluginFile,
    ) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_meta_box(
                'ads-tourism-relationships',
                __('Tourism relationships', 'ads-tourism'),
                [$this, 'render'],
                $contentType->value,
                'normal',
                'default',
            );
        }
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if (!in_array($hookSuffix, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();

        if ($screen === null || ContentType::tryFrom($screen->post_type) === null) {
            return;
        }

        $baseUrl = plugin_dir_url($this->pluginFile);
        wp_enqueue_style(
            'ads-tourism-relationships',
            $baseUrl . 'assets/admin/relationships.css',
            [],
            '0.1.0',
        );
        wp_enqueue_script(
            'ads-tourism-relationships',
            $baseUrl . 'assets/admin/relationships.js',
            [],
            '0.1.0',
            true,
        );
        wp_localize_script('ads-tourism-relationships', 'adsTourismRelationships', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'action' => RelationshipSearchController::ACTION,
            'nonce' => wp_create_nonce(RelationshipSearchController::NONCE_ACTION),
            'strings' => [
                'noResults' => __('No matching records found.', 'ads-tourism'),
                'searchFailed' => __('The relationship search failed.', 'ads-tourism'),
                'primary' => __('Primary', 'ads-tourism'),
                'remove' => __('Remove', 'ads-tourism'),
                'moveUp' => __('Move up', 'ads-tourism'),
                'moveDown' => __('Move down', 'ads-tourism'),
            ],
        ]);
    }

    public function render(WP_Post $post): void
    {
        $contentType = ContentType::tryFrom($post->post_type);

        if ($contentType === null) {
            return;
        }

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<p>';
        echo esc_html__('Search for records, add them once, and ADS Tourism will make the relationship available in both directions.', 'ads-tourism');
        echo '</p>';

        foreach (RelationType::forContentType($contentType) as $relationType) {
            $this->renderControl($post->ID, $contentType, $relationType);
        }
    }

    public function save(int $postId): void
    {
        if (!$this->requestCanSave($postId)) {
            return;
        }

        $contentType = ContentType::tryFrom((string) get_post_type($postId));

        if ($contentType === null) {
            return;
        }

        $submitted = isset($_POST['ads_tourism_relationships']) && is_array($_POST['ads_tourism_relationships'])
            ? wp_unslash($_POST['ads_tourism_relationships'])
            : [];
        $primary = isset($_POST['ads_tourism_primary']) && is_array($_POST['ads_tourism_primary'])
            ? wp_unslash($_POST['ads_tourism_primary'])
            : [];

        foreach (RelationType::forContentType($contentType) as $relationType) {
            $rawIds = $submitted[$relationType->value] ?? [];
            $relatedIds = is_array($rawIds) ? array_values(array_map('absint', $rawIds)) : [];
            $primaryId = isset($primary[$relationType->value])
                ? absint($primary[$relationType->value])
                : null;

            try {
                $this->relationships->replace($postId, $relationType, $relatedIds, $primaryId);
            } catch (InvalidArgumentException) {
                // Ignore a tampered or stale selection and retain existing rows.
            }
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

    private function renderControl(
        int $postId,
        ContentType $contentType,
        RelationType $relationType,
    ): void {
        $recordSide = $relationType->sideFor($contentType);

        if ($recordSide === null) {
            return;
        }

        $selected = $this->relationships->find($postId, $relationType);
        $label = __($relationType->labelFor($contentType), 'ads-tourism');
        echo '<section class="ads-tourism-relation" data-relation-key="' . esc_attr($relationType->value) . '"';
        echo ' data-post-id="' . esc_attr((string) $postId) . '"';
        echo ' data-allows-primary="' . ($relationType->allowsPrimary() ? '1' : '0') . '">';
        echo '<h3>' . esc_html($label) . '</h3>';
        echo '<label class="screen-reader-text" for="ads-tourism-search-' . esc_attr($relationType->value) . '">';
        echo esc_html(sprintf(__('Search %s', 'ads-tourism'), $label));
        echo '</label>';
        echo '<input class="regular-text ads-tourism-relation__search" type="search" ';
        echo 'id="ads-tourism-search-' . esc_attr($relationType->value) . '" ';
        echo 'placeholder="' . esc_attr__('Start typing to search…', 'ads-tourism') . '">';
        echo '<ul class="ads-tourism-relation__results" role="listbox"></ul>';
        echo '<input type="hidden" name="ads_tourism_relationships[' . esc_attr($relationType->value) . '][]" value="">';
        echo '<ol class="ads-tourism-relation__selected">';

        foreach ($selected as $relationship) {
            $this->renderSelectedItem($relationship, $recordSide);
        }

        echo '</ol></section>';
    }

    private function renderSelectedItem(Relationship $relationship, RelationshipSide $recordSide): void
    {
        $relatedPostId = $relationship->relatedPostId($recordSide);
        $relationKey = $relationship->type->value;
        echo '<li data-post-id="' . esc_attr((string) $relatedPostId) . '">';
        echo '<span class="ads-tourism-relation__title">' . esc_html(get_the_title($relatedPostId)) . '</span>';
        echo '<input type="hidden" name="ads_tourism_relationships[' . esc_attr($relationKey) . '][]" value="';
        echo esc_attr((string) $relatedPostId) . '">';

        if ($relationship->type->allowsPrimary()) {
            echo '<label><input type="radio" name="ads_tourism_primary[' . esc_attr($relationKey) . ']" value="';
            echo esc_attr((string) $relatedPostId) . '" ' . checked($relationship->isPrimary, true, false) . '> ';
            echo esc_html__('Primary', 'ads-tourism') . '</label>';
        }

        echo '<span class="ads-tourism-relation__actions">';
        echo '<button type="button" class="button-link ads-tourism-relation__up">' . esc_html__('Move up', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button-link ads-tourism-relation__down">' . esc_html__('Move down', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button-link-delete ads-tourism-relation__remove">' . esc_html__('Remove', 'ads-tourism') . '</button>';
        echo '</span></li>';
    }
}
