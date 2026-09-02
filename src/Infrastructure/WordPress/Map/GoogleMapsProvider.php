<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Application\Map\MapProviderInterface;
use AlefDigitalSolutions\ADSTourism\Application\Map\MapView;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class GoogleMapsProvider implements MapProviderInterface
{
    public const SCRIPT_HANDLE = 'ads-tourism-google-maps';

    public function __construct(private MapSettings $settings) {}

    public function key(): string
    {
        return 'google';
    }

    public function isAvailable(): bool
    {
        return $this->settings->provider() === $this->key() && $this->settings->googleApiKey() !== '';
    }

    public function enqueueAssets(): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        wp_register_script(
            self::SCRIPT_HANDLE,
            'https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($this->settings->googleApiKey()),
            [],
            Plugin::VERSION,
            true,
        );
        wp_enqueue_script(self::SCRIPT_HANDLE);
    }

    public function attribution(): string
    {
        return __('Map data © Google', 'ads-tourism');
    }

    public function scriptDependencies(): array
    {
        return [self::SCRIPT_HANDLE];
    }

    public function normalizeMarker(MapMarker $marker): array
    {
        return $marker->toArray();
    }

    public function renderSingleMarker(MapMarker $marker, MapView $view): string
    {
        return $this->render([$marker], $view);
    }

    public function renderMultipleMarkers(array $markers, MapView $view): string
    {
        return $this->render($markers, $view);
    }

    public function directionsUrl(Coordinates $coordinates): string
    {
        return add_query_arg([
            'api' => '1',
            'destination' => $coordinates->latitude . ',' . $coordinates->longitude,
        ], 'https://www.google.com/maps/dir/');
    }

    /** @param non-empty-list<MapMarker> $markers */
    private function render(array $markers, MapView $view): string
    {
        $data = wp_json_encode(array_map($this->normalizeMarker(...), $markers));

        return '<section class="ads-tourism-map'
            . ($view->cssClass === '' ? '' : ' ' . esc_attr($view->cssClass)) . '"'
            . ' data-ads-tourism-map data-ads-tourism-provider="' . esc_attr($this->key()) . '"'
            . ($view->context === null ? '' : ' data-ads-tourism-context="' . esc_attr($view->context) . '"')
            . ' data-ads-tourism-map-locations="' . esc_attr($view->locationMode) . '"'
            . ' data-markers="' . esc_attr(is_string($data) ? $data : '[]') . '" data-zoom="'
            . esc_attr((string) $view->zoom) . '" style="--ads-tourism-map-height:'
            . esc_attr((string) $view->height) . 'px" aria-label="' . esc_attr($view->accessibleLabel) . '">'
            . '<div class="ads-tourism-map__canvas"></div>'
            . '<p class="ads-tourism-map__attribution">' . esc_html($this->attribution()) . '</p>'
            . '<p class="screen-reader-text" aria-live="polite" aria-atomic="true"></p></section>';
    }
}
