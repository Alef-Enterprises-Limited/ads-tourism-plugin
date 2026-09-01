<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

use AlefDigitalSolutions\ADSTourism\Application\Query\TourismQueryFactory;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ContextComponent;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ShortcodeContextRegistry;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\WordPressQueryService;
use InvalidArgumentException;
use WP_Post;

final class InteractiveShortcodes
{
    /** @var array<string, string> */
    private const LISTING_TYPES = [
        'ads_tourism_places' => 'place',
        'ads_tourism_activities' => 'activity',
        'ads_tourism_stays' => 'stay',
        'ads_tourism_operators' => 'operator',
        'ads_tourism_packages' => 'package',
        'ads_tourism_listing' => 'all',
    ];

    /** @var array<string, TourismTaxonomy> */
    private const TAXONOMY_FIELDS = [
        'place_type' => TourismTaxonomy::PLACE_TYPE,
        'activity_type' => TourismTaxonomy::ACTIVITY_TYPE,
        'stay_type' => TourismTaxonomy::STAY_TYPE,
        'package_type' => TourismTaxonomy::PACKAGE_TYPE,
        'amenity' => TourismTaxonomy::AMENITY,
        'traveller' => TourismTaxonomy::TRAVELLER,
        'accessibility' => TourismTaxonomy::ACCESSIBILITY,
        'tag' => TourismTaxonomy::TOURISM_TAG,
        'area' => TourismTaxonomy::GEOGRAPHIC_AREA,
    ];

    /** @var array<string, ContentType> */
    private const RELATIONSHIP_FIELDS = [
        'place' => ContentType::PLACE,
        'activity' => ContentType::ACTIVITY,
        'stay' => ContentType::STAY,
        'operator' => ContentType::OPERATOR,
        'package' => ContentType::PACKAGE,
        'provider' => ContentType::OPERATOR,
    ];

    private int $controlId = 0;

    public function __construct(
        private readonly TourismQueryFactory $queries,
        private readonly WordPressQueryService $queryService,
        private readonly ShortcodeContextRegistry $contexts,
        private readonly ListingRenderer $listings,
        private readonly PaginationRenderer $pagination,
        private readonly ShortcodeAssets $assets,
        private readonly ShortcodeDiagnostic $diagnostics,
    ) {}

    public function register(): void
    {
        foreach (array_keys(self::LISTING_TYPES) as $shortcode) {
            add_shortcode($shortcode, [$this, 'listing']);
        }

        add_shortcode('ads_tourism_search', [$this, 'search']);
        add_shortcode('ads_tourism_filters', [$this, 'filters']);
        add_shortcode('ads_tourism_sort', [$this, 'sort']);
        add_shortcode('ads_tourism_results', [$this, 'results']);
        add_shortcode('ads_tourism_pagination', [$this, 'pagination']);
    }

    /** @param array<string, mixed> $attributes */
    public function listing(array $attributes, ?string $content = null, string $shortcode = ''): string
    {
        $defaults = $this->listingDefaults();
        $attributes = shortcode_atts($defaults, $attributes, $shortcode);
        $attributes['type'] = self::LISTING_TYPES[$shortcode] ?? (string) $attributes['type'];

        try {
            $context = trim((string) $attributes['context']) === ''
                ? $this->contexts->automatic()
                : new ContextName((string) $attributes['context']);
            $registration = $this->contexts->register($context, ContextComponent::LISTING);

            if (!$registration->accepted) {
                return $this->diagnostics->render($registration->message);
            }

            $query = $this->query($context, $attributes);
            $result = $this->queryService->execute($query);
            $this->contexts->storeResult($context, $result);
            $this->assets->enqueue();
            $class = $this->className((string) $attributes['class']);
            $columns = $this->columns($attributes['columns']);
            $baseUrl = $this->baseUrl();

            return '<section class="ads-tourism-listing' . ($class === '' ? '' : ' ' . esc_attr($class)) . '"'
                . $this->dataAttributes($query, $columns, 'results') . '>'
                . $this->listings->render($result, $context->value, $columns)
                . '<div data-ads-tourism-pagination-container>'
                . $this->pagination->render($result, $query, $baseUrl)
                . '</div></section>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /** @param array<string, mixed> $attributes */
    public function search(array $attributes): string
    {
        try {
            [$context, $query, $class] = $this->control($attributes, ContextComponent::SEARCH);
            $this->assets->enqueue();
            $id = $this->nextId($context, 'search');
            $parameter = $context->parameter('query');

            return '<form class="ads-tourism-control ads-tourism-search' . $class . '" method="get" action="'
                . esc_url($this->baseUrl()) . '" data-ads-tourism-context="' . esc_attr($context->value)
                . '" data-ads-tourism-component="search">'
                . $this->preservedQueryInputs([$parameter, $context->parameter('page')])
                . '<label for="' . esc_attr($id) . '">' . esc_html__('Search tourism', 'ads-tourism') . '</label>'
                . '<div class="ads-tourism-search__row"><input id="' . esc_attr($id) . '" type="search" name="'
                . esc_attr($parameter) . '" value="' . esc_attr($query->keyword) . '" maxlength="100"'
                . ' data-ads-tourism-search-input> <button type="submit">'
                . esc_html__('Search', 'ads-tourism') . '</button></div></form>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /** @param array<string, mixed> $attributes */
    public function sort(array $attributes): string
    {
        try {
            [$context, $query, $class] = $this->control($attributes, ContextComponent::SORT);
            $this->assets->enqueue();
            $id = $this->nextId($context, 'sort');
            $parameter = $context->parameter('sort');
            $html = '<form class="ads-tourism-control ads-tourism-sort' . $class . '" method="get" action="'
                . esc_url($this->baseUrl()) . '" data-ads-tourism-context="' . esc_attr($context->value)
                . '" data-ads-tourism-component="sort">'
                . $this->preservedQueryInputs([$parameter, $context->parameter('page')])
                . '<label for="' . esc_attr($id) . '">' . esc_html__('Sort by', 'ads-tourism') . '</label>'
                . '<select id="' . esc_attr($id) . '" name="' . esc_attr($parameter) . '" data-ads-tourism-sort-select>';

            foreach (QuerySort::labels() as $value => $label) {
                $html .= '<option value="' . esc_attr($value) . '" '
                    . selected($query->sort->value, $value, false) . '>';
                $html .= esc_html(__($label, 'ads-tourism')) . '</option>';
            }

            return $html . '</select><noscript><button type="submit">'
                . esc_html__('Apply', 'ads-tourism') . '</button></noscript></form>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /** @param array<string, mixed> $attributes */
    public function filters(array $attributes): string
    {
        $attributes = shortcode_atts([
            ...$this->listingDefaults(),
            'fields' => 'area,place_type,activity_type,stay_type,package_type,amenity,accessibility',
        ], $attributes, 'ads_tourism_filters');

        try {
            [$context, $query, $class] = $this->control($attributes, ContextComponent::FILTERS);
            $fields = array_values(array_unique(array_filter(array_map(
                static fn(string $field): string => sanitize_key($field),
                explode(',', (string) $attributes['fields']),
            ))));
            $visibleParameters = [$context->parameter('page')];

            foreach ($fields as $field) {
                if (isset(self::TAXONOMY_FIELDS[$field])) {
                    $visibleParameters[] = $context->parameter('tax_' . self::TAXONOMY_FIELDS[$field]->value);
                } elseif (isset(self::RELATIONSHIP_FIELDS[$field])) {
                    $visibleParameters[] = $context->parameter('rel_' . ($field === 'provider' ? 'operator' : $field));
                } elseif ($field === 'price') {
                    $visibleParameters[] = $context->parameter('minimum_price');
                    $visibleParameters[] = $context->parameter('maximum_price');
                } elseif ($field === 'duration') {
                    $visibleParameters[] = $context->parameter('minimum_duration');
                    $visibleParameters[] = $context->parameter('maximum_duration');
                }
            }

            $this->assets->enqueue();
            $html = '<form class="ads-tourism-control ads-tourism-filters' . $class . '" method="get" action="'
                . esc_url($this->baseUrl()) . '" data-ads-tourism-context="' . esc_attr($context->value)
                . '" data-ads-tourism-component="filters"><fieldset><legend>'
                . esc_html__('Filter tourism records', 'ads-tourism') . '</legend>'
                . $this->preservedQueryInputs($visibleParameters);

            foreach ($fields as $field) {
                $html .= $this->filterField($context, $query, $field);
            }

            return $html . '<button type="submit">' . esc_html__('Apply filters', 'ads-tourism')
                . '</button></fieldset></form>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /** @param array<string, mixed> $attributes */
    public function results(array $attributes): string
    {
        $attributes = shortcode_atts($this->listingDefaults(), $attributes, 'ads_tourism_results');

        try {
            $context = $this->requiredContext($attributes);
            $registration = $this->contexts->register($context, ContextComponent::RESULTS);

            if (!$registration->accepted) {
                return $this->diagnostics->render($registration->message);
            }

            $query = $this->query($context, $attributes);
            $result = $this->queryService->execute($query);
            $this->contexts->storeResult($context, $result);
            $this->assets->enqueue();
            $columns = $this->columns($attributes['columns']);
            $class = $this->className((string) $attributes['class']);

            return '<section class="ads-tourism-results-component' . ($class === '' ? '' : ' ' . esc_attr($class))
                . '"' . $this->dataAttributes($query, $columns, 'results') . '>'
                . $this->listings->render($result, $context->value, $columns) . '</section>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /** @param array<string, mixed> $attributes */
    public function pagination(array $attributes): string
    {
        $attributes = shortcode_atts($this->listingDefaults(), $attributes, 'ads_tourism_pagination');

        try {
            $context = $this->requiredContext($attributes);
            $registration = $this->contexts->register($context, ContextComponent::PAGINATION);

            if (!$registration->accepted) {
                return $this->diagnostics->render($registration->message);
            }

            $query = $this->query($context, $attributes);
            $result = $this->contexts->result($context) ?? $this->queryService->execute($query);
            $this->contexts->storeResult($context, $result);
            $this->assets->enqueue();
            $html = $this->pagination->render($result, $query, $this->baseUrl());

            return '<div class="ads-tourism-pagination-component" data-ads-tourism-context="'
                . esc_attr($context->value) . '" data-ads-tourism-component="pagination"'
                . ' data-ads-tourism-pagination-container>' . $html . '</div>';
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array{ContextName, TourismQuery, string}
     */
    private function control(array $attributes, ContextComponent $component): array
    {
        $attributes = shortcode_atts($this->listingDefaults(), $attributes);
        $context = $this->requiredContext($attributes);
        $registration = $this->contexts->register($context, $component);

        if (!$registration->accepted) {
            throw new InvalidArgumentException($registration->message);
        }

        $class = $this->className((string) $attributes['class']);

        return [$context, $this->query($context, $attributes), $class === '' ? '' : ' ' . esc_attr($class)];
    }

    /** @param array<string, mixed> $attributes */
    private function requiredContext(array $attributes): ContextName
    {
        return new ContextName((string) ($attributes['context'] ?? ''));
    }

    /** @param array<string, mixed> $attributes */
    private function query(ContextName $context, array $attributes): TourismQuery
    {
        $taxonomies = [];
        $relationships = [];

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            $value = $this->requestValue($context->parameter('tax_' . $taxonomy->value));

            if ($value !== '') {
                $taxonomies[$taxonomy->value] = explode(',', $value);
            }
        }

        foreach (array_keys(self::RELATIONSHIP_FIELDS) as $field) {
            $field = $field === 'provider' ? 'operator' : $field;
            $value = $this->requestValue($context->parameter('rel_' . $field));

            if ($value !== '') {
                $relationships[$field] = $value;
            }
        }

        return $this->queries->create([
            'context' => $context->value,
            'type' => $attributes['type'] ?? 'all',
            'query' => $this->requestValue($context->parameter('query'), (string) ($attributes['query'] ?? '')),
            'page' => $this->requestValue($context->parameter('page'), '1'),
            'per_page' => $attributes['per_page'] ?? 12,
            'sort' => $this->requestValue($context->parameter('sort'), (string) ($attributes['sort'] ?? 'title_asc')),
            'pagination' => $attributes['pagination'] ?? 'numbered',
            'taxonomies' => $taxonomies,
            'relationships' => $relationships,
            'minimum_price' => $this->requestValue($context->parameter('minimum_price')),
            'maximum_price' => $this->requestValue($context->parameter('maximum_price')),
            'minimum_duration' => $this->requestValue($context->parameter('minimum_duration')),
            'maximum_duration' => $this->requestValue($context->parameter('maximum_duration')),
        ]);
    }

    /** @return array<string, mixed> */
    private function listingDefaults(): array
    {
        return [
            'context' => '',
            'type' => 'all',
            'query' => '',
            'per_page' => 12,
            'columns' => 3,
            'sort' => 'title_asc',
            'pagination' => 'numbered',
            'class' => '',
        ];
    }

    private function filterField(ContextName $context, TourismQuery $query, string $field): string
    {
        if (isset(self::TAXONOMY_FIELDS[$field])) {
            return $this->taxonomyFilter($context, $query, self::TAXONOMY_FIELDS[$field]);
        }

        if (isset(self::RELATIONSHIP_FIELDS[$field])) {
            $key = $field === 'provider' ? 'operator' : $field;

            return $this->relationshipFilter($context, $query, $key, self::RELATIONSHIP_FIELDS[$field]);
        }

        return match ($field) {
            'price' => $this->rangeFilter($context, $query, 'price', __('Price', 'ads-tourism')),
            'duration' => $this->rangeFilter($context, $query, 'duration', __('Duration in days', 'ads-tourism')),
            default => '',
        };
    }

    private function taxonomyFilter(
        ContextName $context,
        TourismQuery $query,
        TourismTaxonomy $taxonomy,
    ): string {
        $terms = get_terms([
            'taxonomy' => $taxonomy->value,
            'hide_empty' => true,
            'number' => 100,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (!is_array($terms) || $terms === []) {
            return '';
        }

        $taxonomyObject = get_taxonomy($taxonomy->value);
        $label = $taxonomyObject === false ? $taxonomy->value : (string) $taxonomyObject->labels->singular_name;
        $name = $context->parameter('tax_' . $taxonomy->value);
        $selectedTerms = $query->taxonomyFilters[$taxonomy->value] ?? [];
        $id = $this->nextId($context, 'filter-' . $taxonomy->value);
        $html = '<label for="' . esc_attr($id) . '">' . esc_html($label) . '</label><select id="'
            . esc_attr($id) . '" name="' . esc_attr($name) . '" data-ads-tourism-filter><option value="">'
            . esc_html__('All', 'ads-tourism') . '</option>';

        foreach ($terms as $term) {
            $html .= '<option value="' . esc_attr($term->slug) . '" '
                . selected(in_array($term->slug, $selectedTerms, true), true, false) . '>'
                . esc_html($term->name) . '</option>';
        }

        return '<div class="ads-tourism-filters__field">' . $html . '</select></div>';
    }

    private function relationshipFilter(
        ContextName $context,
        TourismQuery $query,
        string $key,
        ContentType $type,
    ): string {
        $posts = get_posts([
            'post_type' => $type->value,
            'post_status' => 'publish',
            'numberposts' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        if ($posts === []) {
            return '';
        }

        $name = $context->parameter('rel_' . $key);
        $selectedId = $query->relationshipFilters[$key] ?? 0;
        $id = $this->nextId($context, 'filter-' . $key);
        $html = '<label for="' . esc_attr($id) . '">' . esc_html($this->contentTypeLabel($type))
            . '</label><select id="' . esc_attr($id) . '" name="' . esc_attr($name)
            . '" data-ads-tourism-filter><option value="">' . esc_html__('All', 'ads-tourism') . '</option>';

        foreach ($posts as $post) {
            if ($post instanceof WP_Post) {
                $html .= '<option value="' . esc_attr((string) $post->ID) . '" '
                    . selected($selectedId, $post->ID, false) . '>' . esc_html(get_the_title($post)) . '</option>';
            }
        }

        return '<div class="ads-tourism-filters__field">' . $html . '</select></div>';
    }

    private function rangeFilter(ContextName $context, TourismQuery $query, string $key, string $label): string
    {
        $minimumProperty = $key === 'price' ? $query->minimumPrice : $query->minimumDuration;
        $maximumProperty = $key === 'price' ? $query->maximumPrice : $query->maximumDuration;
        $step = $key === 'price' ? 'any' : '1';

        return '<fieldset class="ads-tourism-filters__range"><legend>' . esc_html($label) . '</legend><label>'
            . esc_html__('Minimum', 'ads-tourism') . ' <input type="number" min="0" step="' . esc_attr($step)
            . '" name="' . esc_attr($context->parameter('minimum_' . $key)) . '" value="'
            . esc_attr($minimumProperty === null ? '' : (string) $minimumProperty)
            . '" data-ads-tourism-filter></label><label>' . esc_html__('Maximum', 'ads-tourism')
            . ' <input type="number" min="0" step="' . esc_attr($step) . '" name="'
            . esc_attr($context->parameter('maximum_' . $key)) . '" value="'
            . esc_attr($maximumProperty === null ? '' : (string) $maximumProperty)
            . '" data-ads-tourism-filter></label></fieldset>';
    }

    private function dataAttributes(TourismQuery $query, int $columns, string $component): string
    {
        $state = wp_json_encode($query->normalizedState());

        return ' data-ads-tourism-context="' . esc_attr($query->context->value)
            . '" data-ads-tourism-component="' . esc_attr($component) . '" data-columns="'
            . esc_attr((string) $columns) . '" data-query="'
            . esc_attr(is_string($state) ? $state : '{}') . '"';
    }

    /** @param list<string> $excluded */
    private function preservedQueryInputs(array $excluded): string
    {
        $html = '';

        foreach ($_GET as $name => $value) {
            if (
                !is_string($name)
                || preg_match('/^[A-Za-z0-9_-]+$/', $name) !== 1
                || in_array($name, $excluded, true)
                || !is_scalar($value)
            ) {
                continue;
            }

            $html .= '<input type="hidden" name="' . esc_attr($name) . '" value="'
                . esc_attr((string) wp_unslash($value)) . '">';
        }

        return $html;
    }

    private function requestValue(string $name, string $default = ''): string
    {
        $value = $_GET[$name] ?? $default;

        return is_scalar($value) ? (string) wp_unslash($value) : $default;
    }

    private function nextId(ContextName $context, string $component): string
    {
        ++$this->controlId;

        return 'ads-tourism-' . strtolower($context->value) . '-' . sanitize_key($component) . '-' . $this->controlId;
    }

    private function columns(mixed $value): int
    {
        $columns = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($columns) ? min(6, max(1, $columns)) : 3;
    }

    private function baseUrl(): string
    {
        $permalink = get_permalink();

        return is_string($permalink) ? $permalink : home_url('/');
    }

    private function className(string $className): string
    {
        return implode(' ', array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($className)) ?: [],
        )));
    }

    private function contentTypeLabel(ContentType $type): string
    {
        return match ($type) {
            ContentType::PLACE => __('Place', 'ads-tourism'),
            ContentType::ACTIVITY => __('Activity', 'ads-tourism'),
            ContentType::STAY => __('Stay', 'ads-tourism'),
            ContentType::OPERATOR => __('Operator', 'ads-tourism'),
            ContentType::PACKAGE => __('Package', 'ads-tourism'),
        };
    }
}
