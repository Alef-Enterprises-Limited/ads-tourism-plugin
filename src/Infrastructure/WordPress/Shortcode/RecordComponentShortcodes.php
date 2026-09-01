<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;
use WP_Post;

final readonly class RecordComponentShortcodes
{
    /** @var array<string, ContentType> */
    private const RELATED_SHORTCODES = [
        'ads_tourism_related_places' => ContentType::PLACE,
        'ads_tourism_related_activities' => ContentType::ACTIVITY,
        'ads_tourism_related_stays' => ContentType::STAY,
        'ads_tourism_related_operators' => ContentType::OPERATOR,
        'ads_tourism_related_packages' => ContentType::PACKAGE,
    ];

    public function __construct(
        private RecordFieldSchema $fields,
        private RelationshipRepository $relationships,
        private FrontendRenderer $frontend,
        private ShortcodeAssets $assets,
        private ShortcodeDiagnostic $diagnostics,
        private TranslationResolver $translations,
    ) {}

    public function register(): void
    {
        add_shortcode('ads_tourism_field', [$this, 'field']);
        add_shortcode('ads_tourism_gallery', [$this, 'gallery']);
        add_shortcode('ads_tourism_package_itinerary', [$this, 'itinerary']);
        add_shortcode('ads_tourism_package_provider', [$this, 'provider']);

        foreach (array_keys(self::RELATED_SHORTCODES) as $shortcode) {
            add_shortcode($shortcode, [$this, 'related']);
        }
    }

    /** @param array<string, mixed> $attributes */
    public function field(array $attributes): string
    {
        $attributes = shortcode_atts(['field' => '', 'id' => 0, 'label' => 'false', 'class' => ''], $attributes);
        $postId = $this->postId($attributes);
        $contentType = ContentType::tryFrom((string) get_post_type($postId));
        $key = sanitize_key((string) $attributes['field']);
        $key = str_starts_with($key, 'ads_tourism_') ? $key : 'ads_tourism_' . $key;
        $field = $contentType !== null ? $this->fields->find($contentType, $key) : null;

        if ($postId < 1 || $field === null || $field->administratorsOnly || !$this->isSimple($field)) {
            return $this->diagnostics->render(__('The requested public tourism field is unavailable.', 'ads-tourism'));
        }

        $value = apply_filters('ads_tourism_resolved_field', null, $postId, $field->key);

        if ($value === null || $value === '' || $value === []) {
            return '';
        }

        $class = $this->className((string) $attributes['class']);
        $html = '<span class="ads-tourism-field' . ($class === '' ? '' : ' ' . esc_attr($class)) . '">';

        if (filter_var($attributes['label'], FILTER_VALIDATE_BOOLEAN)) {
            $html .= '<span class="ads-tourism-field__label">';
            $html .= esc_html(__($field->label, 'ads-tourism')) . '</span> ';
        }

        return $html . '<span class="ads-tourism-field__value">' . $this->formatField($field, $value) . '</span></span>';
    }

    /** @param array<string, mixed> $attributes */
    public function gallery(array $attributes): string
    {
        $attributes = shortcode_atts([
            'id' => 0,
            'limit' => '',
            'columns' => '',
            'role' => '',
            'order' => '',
            'size' => '',
            'captions' => '',
            'credits' => '',
            'lightbox' => '',
            'class' => '',
        ], $attributes, 'ads_tourism_gallery');
        $postId = $this->postId($attributes);

        if ($postId < 1 || ContentType::tryFrom((string) get_post_type($postId)) === null) {
            return $this->diagnostics->render(__('A valid tourism record is required for the gallery.', 'ads-tourism'));
        }

        $this->assets->enqueue();
        $attributes['class'] = $this->className((string) $attributes['class']);
        ob_start();
        $this->frontend->renderGallery($postId, $attributes);

        return (string) ob_get_clean();
    }

    /** @param array<string, mixed> $attributes */
    public function related(array $attributes, ?string $content = null, string $shortcode = ''): string
    {
        $targetType = self::RELATED_SHORTCODES[$shortcode] ?? null;
        $postId = $this->postId(shortcode_atts(['id' => 0, 'class' => ''], $attributes));
        $sourceType = ContentType::tryFrom((string) get_post_type($postId));

        if ($postId < 1 || $sourceType === null || $targetType === null) {
            return $this->diagnostics->render(__('A valid tourism relationship component is required.', 'ads-tourism'));
        }

        return $this->relatedList($postId, $sourceType, $targetType, (string) ($attributes['class'] ?? ''));
    }

    /** @param array<string, mixed> $attributes */
    public function itinerary(array $attributes): string
    {
        $postId = $this->postId(shortcode_atts(['id' => 0, 'class' => ''], $attributes));

        if (get_post_type($postId) !== ContentType::PACKAGE->value) {
            return $this->diagnostics->render(__('A Package is required for the itinerary component.', 'ads-tourism'));
        }

        $items = get_post_meta($postId, 'ads_tourism_itinerary', true);

        if (!is_array($items) || $items === []) {
            return '';
        }

        $class = $this->className((string) ($attributes['class'] ?? ''));
        $html = '<ol class="ads-tourism-itinerary' . ($class === '' ? '' : ' ' . esc_attr($class)) . '">';

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = sanitize_text_field((string) ($item['title'] ?? $item['name'] ?? ''));
            $description = sanitize_textarea_field((string) ($item['description'] ?? ''));
            $time = sanitize_text_field((string) ($item['time'] ?? $item['day'] ?? ''));

            if ($title === '' && $description === '' && $time === '') {
                continue;
            }

            $html .= '<li class="ads-tourism-itinerary__item">';

            if ($time !== '') {
                $html .= '<span class="ads-tourism-itinerary__time">' . esc_html($time) . '</span>';
            }

            if ($title !== '') {
                $html .= '<strong class="ads-tourism-itinerary__title">' . esc_html($title) . '</strong>';
            }

            if ($description !== '') {
                $html .= '<p>' . nl2br(esc_html($description)) . '</p>';
            }

            $html .= '</li>';
        }

        return $html . '</ol>';
    }

    /** @param array<string, mixed> $attributes */
    public function provider(array $attributes): string
    {
        $postId = $this->postId(shortcode_atts(['id' => 0, 'class' => ''], $attributes));

        if (get_post_type($postId) !== ContentType::PACKAGE->value) {
            return $this->diagnostics->render(__('A Package is required for the provider component.', 'ads-tourism'));
        }

        return $this->relatedList(
            $postId,
            ContentType::PACKAGE,
            [ContentType::OPERATOR, ContentType::STAY],
            (string) ($attributes['class'] ?? ''),
            [RelationType::PACKAGE_OFFERED_BY, RelationType::PACKAGE_PARTNER_PROVIDER],
        );
    }

    /**
     * @param ContentType|list<ContentType> $targetTypes
     * @param list<RelationType>|null       $allowedRelations
     */
    private function relatedList(
        int $postId,
        ContentType $sourceType,
        ContentType|array $targetTypes,
        string $className,
        ?array $allowedRelations = null,
    ): string {
        $posts = [];
        $targetTypes = is_array($targetTypes) ? $targetTypes : [$targetTypes];
        $targetTypeValues = array_map(static fn(ContentType $type): string => $type->value, $targetTypes);

        foreach (RelationType::forContentType($sourceType) as $relationType) {
            if ($allowedRelations !== null && !in_array($relationType, $allowedRelations, true)) {
                continue;
            }

            $side = $relationType->sideFor($sourceType);

            if ($side === null) {
                continue;
            }

            foreach ($this->relationships->findForRecord($postId, $relationType, $side) as $relationship) {
                $relatedId = $relationship->relatedPostId($side);
                $relatedType = (string) get_post_type($relatedId);
                $relatedId = $this->translations->postId($relatedId, $relatedType) ?? 0;
                $related = get_post($relatedId);

                if (
                    $related instanceof WP_Post
                    && $related->post_status === 'publish'
                    && in_array($related->post_type, $targetTypeValues, true)
                ) {
                    $posts[$related->ID] = $related;
                }
            }
        }

        if ($posts === []) {
            return '';
        }

        $class = $this->className($className);
        $html = '<ul class="ads-tourism-related-list' . ($class === '' ? '' : ' ' . esc_attr($class)) . '">';

        foreach ($posts as $post) {
            $url = get_permalink($post);
            $html .= '<li class="ads-tourism-related-list__item">';
            $html .= is_string($url)
                ? '<a href="' . esc_url($url) . '">' . esc_html(get_the_title($post)) . '</a>'
                : esc_html(get_the_title($post));
            $html .= '</li>';
        }

        return $html . '</ul>';
    }

    /** @param array<string, mixed> $attributes */
    private function postId(array $attributes): int
    {
        $explicit = absint($attributes['id'] ?? 0);

        return $explicit > 0 ? $explicit : get_the_ID();
    }

    private function isSimple(FieldDefinition $field): bool
    {
        return $field->type !== FieldType::ARRAY && $field->type !== FieldType::OBJECT;
    }

    private function formatField(FieldDefinition $field, mixed $value): string
    {
        if ($field->type === FieldType::BOOLEAN) {
            return esc_html((bool) $value ? __('Yes', 'ads-tourism') : __('No', 'ads-tourism'));
        }

        $text = is_scalar($value) ? (string) $value : '';
        $text = $field->type === FieldType::SELECT && isset($field->options[$text])
            ? __($field->options[$text], 'ads-tourism')
            : $text;

        if ($field->type === FieldType::URL && wp_http_validate_url($text) !== false) {
            return '<a href="' . esc_url($text) . '">' . esc_html($text) . '</a>';
        }

        if ($field->type === FieldType::EMAIL && is_email($text)) {
            return '<a href="mailto:' . esc_attr($text) . '">' . esc_html($text) . '</a>';
        }

        return nl2br(esc_html($text));
    }

    private function className(string $className): string
    {
        return implode(' ', array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($className)) ?: [],
        )));
    }
}
