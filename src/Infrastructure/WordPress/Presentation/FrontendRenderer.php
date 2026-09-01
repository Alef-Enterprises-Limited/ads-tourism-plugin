<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;
use AlefDigitalSolutions\ADSTourism\Domain\Presentation\CustomContentPosition;
use AlefDigitalSolutions\ADSTourism\Domain\Presentation\LayoutMode;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\FeaturedMediaResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use WP_Post;
use WP_Term;

final readonly class FrontendRenderer
{
    /** @var list<string> */
    private const INTERNAL_FIELDS = [
        'ads_tourism_external_id',
        'ads_tourism_layout_mode',
        'ads_tourism_custom_content_position',
        'ads_tourism_verification_status',
        'ads_tourism_source_name',
        'ads_tourism_source_reference',
        'ads_tourism_source_url',
        'ads_tourism_date_collected',
        'ads_tourism_last_verified_at',
        'ads_tourism_verified_by_user_id',
        'ads_tourism_verification_notes',
        'ads_tourism_publication_notes',
        'ads_tourism_manual_order',
        'ads_tourism_external_featured_media_url',
        'ads_tourism_external_featured_media_url_type',
        'ads_tourism_display_fallback_overrides',
        'ads_tourism_seo_schema_override',
        'ads_tourism_gallery_max_images',
        'ads_tourism_gallery_columns',
        'ads_tourism_gallery_order',
        'ads_tourism_gallery_role_filter',
        'ads_tourism_gallery_image_size',
        'ads_tourism_gallery_show_captions',
        'ads_tourism_gallery_show_credits',
        'ads_tourism_gallery_lightbox',
        'ads_tourism_gallery_include_featured',
        'ads_tourism_gallery_pagination',
    ];

    public function __construct(
        private string $pluginFile,
        private RecordFieldSchema $fields,
        private FeaturedMediaResolver $featuredMedia,
        private MediaLinkRepository $media,
        private RelationshipRepository $relationships,
        private TranslationResolver $translations,
    ) {}

    public function register(): void
    {
        add_filter('ads_tourism_frontend_renderer', [$this, 'provide']);
    }

    public function provide(mixed $renderer): self
    {
        return $this;
    }

    public function renderSingle(int $postId): void
    {
        $contentType = ContentType::tryFrom((string) get_post_type($postId));

        if ($contentType === null) {
            return;
        }

        $layout = LayoutMode::fromStoredValue(get_post_meta($postId, 'ads_tourism_layout_mode', true));
        $position = CustomContentPosition::fromStoredValue(
            get_post_meta($postId, 'ads_tourism_custom_content_position', true),
        );

        do_action('ads_tourism_before_record', $postId, $layout->value);
        echo '<article class="ads-tourism-record ads-tourism-record--' . esc_attr($layout->value) . '">';

        if ($layout === LayoutMode::FULL_CUSTOM) {
            $this->renderCustomContent($postId);
            echo '</article>';
            do_action('ads_tourism_after_record', $postId, $layout->value);

            return;
        }

        echo '<header class="ads-tourism-record__header">';
        $title = get_the_title($postId);

        if ($title !== '') {
            echo '<h1 class="ads-tourism-record__title">' . esc_html($title) . '</h1>';
        }

        $this->renderFeaturedMedia($postId, 'large', 'ads-tourism-record__hero');
        echo '</header>';

        $summary = apply_filters('ads_tourism_resolved_field', null, $postId, 'ads_tourism_summary');

        if (is_string($summary) && trim($summary) !== '') {
            echo '<div class="ads-tourism-record__summary">' . wp_kses_post(wpautop($summary)) . '</div>';
        }

        if ($layout->includesCustomContent() && $position === CustomContentPosition::BEFORE) {
            $this->renderCustomContent($postId);
        }

        do_action('ads_tourism_before_structured_content', $postId);
        $this->renderDetails($postId, $contentType);

        if ($layout->includesCustomContent() && $position === CustomContentPosition::TEMPLATE_SLOT) {
            $this->renderCustomContent($postId, 'template-slot');
        }

        $this->renderTaxonomies($postId, $contentType);
        $this->renderRelationships($postId, $contentType);
        $this->renderGallery($postId);
        do_action('ads_tourism_after_structured_content', $postId);

        if ($layout->includesCustomContent() && $position === CustomContentPosition::AFTER) {
            $this->renderCustomContent($postId);
        }

        echo '</article>';
        do_action('ads_tourism_after_record', $postId, $layout->value);
    }

    public function renderArchive(): void
    {
        echo '<main class="ads-tourism-archive">';
        echo '<header class="ads-tourism-archive__header">';
        echo '<h1 class="ads-tourism-archive__title">' . wp_kses_post(get_the_archive_title()) . '</h1>';
        $description = get_the_archive_description();

        if ($description !== '') {
            echo '<div class="ads-tourism-archive__description">' . wp_kses_post($description) . '</div>';
        }

        echo '</header>';

        if (have_posts()) {
            echo '<div class="ads-tourism-grid" role="list">';

            while (have_posts()) {
                the_post();
                $this->renderCard(get_the_ID());
            }

            echo '</div>';
            the_posts_pagination([
                'mid_size' => 2,
                'prev_text' => __('Previous', 'ads-tourism'),
                'next_text' => __('Next', 'ads-tourism'),
                'screen_reader_text' => __('Tourism record navigation', 'ads-tourism'),
            ]);
        } else {
            $this->includeComponent('no-results');
        }

        echo '</main>';
    }

    public function renderFeaturedMedia(int $postId, string $size, string $className): void
    {
        $media = $this->featuredMedia->resolve($postId);

        if ($media === null) {
            return;
        }

        $image = $this->resolvedMediaImage($media, $postId, $size, $className . '__image');

        if ($image !== '') {
            echo '<figure class="' . esc_attr($className) . '">' . $image . '</figure>';
        }
    }

    private function renderCustomContent(int $postId, string $modifier = 'custom'): void
    {
        $content = (string) get_post_field('post_content', $postId);

        if (trim($content) === '') {
            return;
        }

        $rendered = apply_filters('the_content', $content);
        echo '<div class="ads-tourism-record__custom-content ads-tourism-record__custom-content--';
        echo esc_attr($modifier) . '">';

        // WordPress and the active builder own the_content filtering and output safety.
        echo is_string($rendered) ? $rendered : '';
        echo '</div>';
    }

    private function renderDetails(int $postId, ContentType $contentType): void
    {
        $rows = [];

        foreach ($this->fields->for($contentType) as $field) {
            if (!$this->fieldIsPublic($field)) {
                continue;
            }

            $value = apply_filters('ads_tourism_resolved_field', null, $postId, $field->key);

            if ($this->isEmpty($value)) {
                continue;
            }

            $rows[] = [$field, $value];
        }

        if ($rows === []) {
            return;
        }

        echo '<section class="ads-tourism-record__section ads-tourism-details" aria-labelledby="ads-tourism-details-title">';
        echo '<h2 id="ads-tourism-details-title" class="ads-tourism-record__section-title">';
        echo esc_html__('Details', 'ads-tourism') . '</h2><dl class="ads-tourism-details__list">';

        foreach ($rows as [$field, $value]) {
            echo '<div class="ads-tourism-details__item"><dt>';
            echo esc_html(__($field->label, 'ads-tourism')) . '</dt><dd>';
            $this->renderFieldValue($field, $value);
            echo '</dd></div>';
        }

        echo '</dl></section>';
    }

    private function renderTaxonomies(int $postId, ContentType $contentType): void
    {
        $groups = [];

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            if (!in_array($contentType->value, $taxonomy->objectTypes(), true)) {
                continue;
            }

            $terms = get_the_terms($postId, $taxonomy->value);

            if (!is_array($terms) || $terms === []) {
                continue;
            }

            $translatedTerms = [];

            foreach ($terms as $term) {
                if (!$term instanceof WP_Term) {
                    continue;
                }

                $translatedId = $this->translations->termId($term->term_id, $taxonomy->value);
                $translated = $translatedId === null ? null : get_term($translatedId, $taxonomy->value);

                if ($translated instanceof WP_Term) {
                    $translatedTerms[$translated->term_id] = $translated;
                }
            }

            if ($translatedTerms === []) {
                continue;
            }

            $taxonomyObject = get_taxonomy($taxonomy->value);
            $label = $taxonomyObject === false ? $taxonomy->value : (string) $taxonomyObject->labels->name;
            $groups[] = [$label, $taxonomy, array_values($translatedTerms)];
        }

        if ($groups === []) {
            return;
        }

        echo '<section class="ads-tourism-record__section ads-tourism-taxonomies" aria-labelledby="ads-tourism-categories-title">';
        echo '<h2 id="ads-tourism-categories-title" class="ads-tourism-record__section-title">';
        echo esc_html__('Categories', 'ads-tourism') . '</h2>';

        foreach ($groups as [$label, $taxonomy, $terms]) {
            echo '<div class="ads-tourism-taxonomies__group"><h3>' . esc_html($label) . '</h3><ul>';

            foreach ($terms as $term) {
                $url = get_term_link($term, $taxonomy->value);
                echo '<li>';

                if (is_string($url)) {
                    echo '<a href="' . esc_url($url) . '">' . esc_html($term->name) . '</a>';
                } else {
                    echo esc_html($term->name);
                }

                echo '</li>';
            }

            echo '</ul></div>';
        }

        echo '</section>';
    }

    private function renderRelationships(int $postId, ContentType $contentType): void
    {
        $groups = [];

        foreach (RelationType::forContentType($contentType) as $relationType) {
            $side = $relationType->sideFor($contentType);

            if ($side === null) {
                continue;
            }

            $records = [];

            foreach ($this->relationships->findForRecord($postId, $relationType, $side) as $relationship) {
                $relatedId = $relationship->relatedPostId($side);
                $relatedType = (string) get_post_type($relatedId);
                $relatedId = $this->translations->postId($relatedId, $relatedType) ?? 0;
                $post = get_post($relatedId);

                if ($post instanceof WP_Post && $post->post_status === 'publish') {
                    $records[] = $post;
                }
            }

            if ($records !== []) {
                $groups[] = [__($relationType->labelFor($contentType), 'ads-tourism'), $records];
            }
        }

        if ($groups === []) {
            return;
        }

        echo '<section class="ads-tourism-record__section ads-tourism-related" aria-labelledby="ads-tourism-related-title">';
        echo '<h2 id="ads-tourism-related-title" class="ads-tourism-record__section-title">';
        echo esc_html__('Related tourism', 'ads-tourism') . '</h2>';

        foreach ($groups as [$label, $records]) {
            echo '<div class="ads-tourism-related__group"><h3>' . esc_html($label) . '</h3><ul>';

            foreach ($records as $record) {
                $url = get_permalink($record);
                echo '<li>';

                if (is_string($url)) {
                    echo '<a href="' . esc_url($url) . '">' . esc_html(get_the_title($record)) . '</a>';
                } else {
                    echo esc_html(get_the_title($record));
                }

                echo '</li>';
            }

            echo '</ul></div>';
        }

        echo '</section>';
    }

    /** @param array<string, mixed> $overrides */
    public function renderGallery(int $postId, array $overrides = []): void
    {
        $roleValue = $this->galleryStringOption($overrides, 'role', $postId, 'gallery_role_filter');
        $role = $roleValue === '' || $roleValue === 'all' ? null : MediaRole::tryFrom($roleValue);

        if ($roleValue !== '' && $roleValue !== 'all' && $role === null) {
            return;
        }

        $links = $this->media->findForEntity($postId, $role);

        if ($links === []) {
            return;
        }

        $order = $this->galleryStringOption($overrides, 'order', $postId, 'gallery_order');

        if ($order === 'newest') {
            $links = array_reverse($links);
        } elseif ($order === 'random') {
            shuffle($links);
        }

        $maximum = $this->galleryIntegerOption($overrides, 'limit', $postId, 'gallery_max_images', 0, 100);

        if ($maximum > 0) {
            $links = array_slice($links, 0, $maximum);
        }

        $columns = $this->galleryIntegerOption($overrides, 'columns', $postId, 'gallery_columns', 1, 6) ?: 3;
        $size = sanitize_key($this->galleryStringOption($overrides, 'size', $postId, 'gallery_image_size'));
        $size = $size === '' ? 'large' : $size;
        $showCaptions = $this->galleryBooleanOption($overrides, 'captions', $postId, 'gallery_show_captions');
        $showCredits = $this->galleryBooleanOption($overrides, 'credits', $postId, 'gallery_show_credits');
        $lightbox = $this->galleryBooleanOption($overrides, 'lightbox', $postId, 'gallery_lightbox');
        $class = isset($overrides['class']) && is_string($overrides['class']) ? trim($overrides['class']) : '';
        $rendered = 0;

        ob_start();

        foreach ($links as $link) {
            $rendered += $this->renderGalleryItem($link, $size, $showCaptions, $showCredits, $lightbox) ? 1 : 0;
        }

        $items = (string) ob_get_clean();

        if ($rendered === 0) {
            return;
        }

        echo '<section class="ads-tourism-record__section ads-tourism-gallery';
        echo $class === '' ? '' : ' ' . esc_attr($class);
        echo '" aria-label="' . esc_attr__('Gallery', 'ads-tourism') . '">';
        echo '<h2 class="ads-tourism-record__section-title">';
        echo esc_html__('Gallery', 'ads-tourism') . '</h2>';
        echo '<div class="ads-tourism-gallery__grid" style="--ads-tourism-gallery-columns:';
        echo esc_attr((string) $columns) . '">' . $items . '</div></section>';
    }

    /** @param array<string, mixed> $overrides */
    private function galleryStringOption(
        array $overrides,
        string $key,
        int $postId,
        string $metaKey,
    ): string {
        $override = $overrides[$key] ?? null;

        return is_scalar($override) && (string) $override !== ''
            ? (string) $override
            : (string) get_post_meta($postId, 'ads_tourism_' . $metaKey, true);
    }

    /** @param array<string, mixed> $overrides */
    private function galleryIntegerOption(
        array $overrides,
        string $key,
        int $postId,
        string $metaKey,
        int $minimum,
        int $maximum,
    ): int {
        $value = $this->galleryStringOption($overrides, $key, $postId, $metaKey);
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) ? min($maximum, max($minimum, $integer)) : $minimum;
    }

    /** @param array<string, mixed> $overrides */
    private function galleryBooleanOption(
        array $overrides,
        string $key,
        int $postId,
        string $metaKey,
    ): bool {
        $value = $this->galleryStringOption($overrides, $key, $postId, $metaKey);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function renderGalleryItem(
        MediaLink $link,
        string $size,
        bool $showCaptions,
        bool $showCredits,
        bool $lightbox,
    ): bool {
        $url = $this->mediaLinkUrl($link, $size);

        if ($url === '') {
            return false;
        }

        $alt = $link->customAltText !== '' ? $link->customAltText : $link->customTitle;
        $image = '<img class="ads-tourism-gallery__image" src="' . esc_url($url) . '" alt="';
        $image .= esc_attr($alt) . '" loading="lazy">';
        $fullUrl = $link->attachmentId !== null
            ? (string) wp_get_attachment_image_url($link->attachmentId, 'full')
            : $url;
        echo '<figure class="ads-tourism-gallery__item">';

        if ($lightbox && $fullUrl !== '') {
            echo '<a href="' . esc_url($fullUrl) . '" class="ads-tourism-gallery__link">' . $image . '</a>';
        } else {
            echo $image;
        }

        if (($showCaptions && $link->customCaption !== '') || ($showCredits && $link->credit !== '')) {
            echo '<figcaption class="ads-tourism-gallery__caption">';

            if ($showCaptions && $link->customCaption !== '') {
                echo wp_kses_post($link->customCaption);
            }

            if ($showCredits && $link->credit !== '') {
                echo '<small>' . esc_html($link->credit) . '</small>';
            }

            echo '</figcaption>';
        }

        echo '</figure>';

        return true;
    }

    public function renderCard(int $postId): void
    {
        $renderer = $this;
        $this->includeComponent('card', compact('postId', 'renderer'));
    }

    /** @param array<string, mixed> $variables */
    private function includeComponent(string $component, array $variables = []): void
    {
        extract($variables, EXTR_SKIP);
        include plugin_dir_path($this->pluginFile) . 'templates/components/' . $component . '.php';
    }

    private function resolvedMediaImage(
        ResolvedMedia $media,
        int $postId,
        string $size,
        string $className,
    ): string {
        if ($media->attachmentId !== null) {
            return (string) wp_get_attachment_image($media->attachmentId, $size, false, [
                'class' => $className,
                'loading' => 'eager',
            ]);
        }

        return $media->url === null
            ? ''
            : '<img class="' . esc_attr($className) . '" src="' . esc_url($media->url) . '" alt="'
                . esc_attr(get_the_title($postId)) . '" loading="eager">';
    }

    private function mediaLinkUrl(MediaLink $link, string $size): string
    {
        if ($link->attachmentId !== null) {
            return (string) wp_get_attachment_image_url($link->attachmentId, $size);
        }

        if ($link->mediaUrl === null) {
            return '';
        }

        $url = $link->urlType === MediaUrlType::RELATIVE ? home_url($link->mediaUrl) : $link->mediaUrl;

        return wp_http_validate_url($url) === false ? '' : $url;
    }

    private function fieldIsPublic(FieldDefinition $field): bool
    {
        return !$field->administratorsOnly
            && !in_array($field->key, self::INTERNAL_FIELDS, true)
            && $field->type !== FieldType::ARRAY
            && $field->type !== FieldType::OBJECT;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function renderFieldValue(FieldDefinition $field, mixed $value): void
    {
        if ($field->type === FieldType::BOOLEAN) {
            echo esc_html((bool) $value ? __('Yes', 'ads-tourism') : __('No', 'ads-tourism'));

            return;
        }

        $text = is_scalar($value) ? (string) $value : '';

        if ($field->type === FieldType::SELECT && isset($field->options[$text])) {
            $text = __($field->options[$text], 'ads-tourism');
        }

        if ($field->type === FieldType::URL && wp_http_validate_url($text) !== false) {
            echo '<a href="' . esc_url($text) . '">' . esc_html($text) . '</a>';

            return;
        }

        if ($field->type === FieldType::EMAIL && is_email($text)) {
            echo '<a href="mailto:' . esc_attr($text) . '">' . esc_html($text) . '</a>';

            return;
        }

        echo nl2br(esc_html($text));
    }
}
