<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Application\Map\MapView;
use AlefDigitalSolutions\ADSTourism\Application\Query\TourismQueryFactory;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ContextComponent;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ShortcodeContextRegistry;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\WordPressQueryService;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ShortcodeDiagnostic;
use InvalidArgumentException;

final readonly class MapShortcodes
{
    public function __construct(
        private MapProviderRegistry $providers,
        private MapMarkerFactory $markers,
        private MapAssets $assets,
        private TourismQueryFactory $queries,
        private WordPressQueryService $queryService,
        private ShortcodeContextRegistry $contexts,
        private ShortcodeDiagnostic $diagnostics,
    ) {}

    public function register(): void
    {
        add_shortcode('ads_tourism_map', [$this, 'map']);
    }

    /** @param array<string, mixed> $attributes */
    public function map(array $attributes): string
    {
        $attributes = shortcode_atts([
            'id' => 0,
            'ids' => '',
            'context' => '',
            'type' => 'all',
            'query' => '',
            'per_page' => 24,
            'sort' => 'title_asc',
            'zoom' => 0,
            'height' => 420,
            'marker_limit' => 100,
            'fallback' => 'none',
            'class' => '',
        ], $attributes, 'ads_tourism_map');

        try {
            $contextValue = trim((string) $attributes['context']);
            $context = $contextValue === '' ? null : new ContextName($contextValue);
            $markers = $context === null
                ? $this->explicitMarkers($attributes)
                : $this->contextMarkers($context, $attributes);
            $provider = $this->providers->selected();

            if ($provider === null) {
                return $this->fallback($markers, (string) $attributes['fallback']);
            }

            if ($markers === []) {
                return '';
            }

            $this->assets->enqueue($provider);
            $height = $this->boundedInteger($attributes['height'], 200, 1000, 420);
            $zoom = $this->boundedInteger($attributes['zoom'], 0, 22, 0);
            $class = $this->className((string) $attributes['class']);
            $view = new MapView(
                $height,
                $zoom,
                $class,
                __('Tourism map', 'ads-tourism'),
                $context?->value,
            );

            return count($markers) === 1
                ? $provider->renderSingleMarker($markers[0], $view)
                : $provider->renderMultipleMarkers($markers, $view);
        } catch (InvalidArgumentException $exception) {
            return $this->diagnostics->render($exception->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return list<MapMarker>
     */
    private function explicitMarkers(array $attributes): array
    {
        $postIds = [];
        $singleId = absint($attributes['id']);

        if ($singleId > 0) {
            $postIds[] = $singleId;
        } elseif (trim((string) $attributes['ids']) !== '') {
            foreach (explode(',', (string) $attributes['ids']) as $postId) {
                $postId = absint($postId);

                if ($postId > 0) {
                    $postIds[] = $postId;
                }
            }
        } else {
            $postIds[] = get_the_ID();
        }

        return $this->markers->forPosts(
            array_values(array_unique($postIds)),
            $this->boundedInteger($attributes['marker_limit'], 1, 100, 100),
        );
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return list<MapMarker>
     */
    private function contextMarkers(ContextName $context, array $attributes): array
    {
        $registration = $this->contexts->register($context, ContextComponent::MAP);

        if (!$registration->accepted) {
            throw new InvalidArgumentException($registration->message);
        }

        $result = $this->contexts->result($context);

        if ($result === null) {
            $result = $this->queryService->execute($this->queries->create([
                'context' => $context->value,
                'type' => $attributes['type'],
                'query' => $this->requestValue($context->parameter('query'), (string) $attributes['query']),
                'page' => 1,
                'per_page' => $attributes['per_page'],
                'sort' => $this->requestValue($context->parameter('sort'), (string) $attributes['sort']),
                'pagination' => 'none',
                'taxonomies' => $this->taxonomyFilters($context),
                'relationships' => $this->relationshipFilters($context),
                'minimum_price' => $this->requestValue($context->parameter('minimum_price')),
                'maximum_price' => $this->requestValue($context->parameter('maximum_price')),
                'minimum_duration' => $this->requestValue($context->parameter('minimum_duration')),
                'maximum_duration' => $this->requestValue($context->parameter('maximum_duration')),
            ]));
            $this->contexts->storeResult($context, $result);
        }

        return $this->markers->forPosts(
            $result->postIds,
            $this->boundedInteger($attributes['marker_limit'], 1, 100, 100),
        );
    }

    /** @return array<string, list<string>> */
    private function taxonomyFilters(ContextName $context): array
    {
        $filters = [];

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            $value = $this->requestValue($context->parameter('tax_' . $taxonomy->value));

            if ($value !== '') {
                $filters[$taxonomy->value] = explode(',', $value);
            }
        }

        return $filters;
    }

    /** @return array<string, string> */
    private function relationshipFilters(ContextName $context): array
    {
        $filters = [];

        foreach (['place', 'activity', 'stay', 'operator', 'package'] as $type) {
            $value = $this->requestValue($context->parameter('rel_' . $type));

            if ($value !== '') {
                $filters[$type] = $value;
            }
        }

        return $filters;
    }

    /** @param list<MapMarker> $markers */
    private function fallback(array $markers, string $mode): string
    {
        if ($mode !== 'directions' || count($markers) !== 1) {
            return '';
        }

        $marker = $markers[0];
        $url = add_query_arg([
            'api' => 1,
            'destination' => $marker->coordinates->latitude . ',' . $marker->coordinates->longitude,
        ], 'https://www.google.com/maps/dir/');

        return '<a class="ads-tourism-map-directions" href="' . esc_url($url) . '" rel="noopener noreferrer">'
            . esc_html__('Get directions', 'ads-tourism') . '</a>';
    }

    private function requestValue(string $name, string $default = ''): string
    {
        $value = $_GET[$name] ?? $default;

        return is_scalar($value) ? (string) wp_unslash($value) : $default;
    }

    private function boundedInteger(mixed $value, int $minimum, int $maximum, int $default): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($integer) ? min($maximum, max($minimum, $integer)) : $default;
    }

    private function className(string $className): string
    {
        return implode(' ', array_filter(array_map(
            'sanitize_html_class',
            preg_split('/\s+/', trim($className)) ?: [],
        )));
    }
}
