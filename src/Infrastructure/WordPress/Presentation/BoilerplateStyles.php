<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

final readonly class BoilerplateStyles
{
    /** @var array<string, array{label: string, file: string, group: string}> */
    private const SCOPES = [
        'global' => [
            'label' => 'Global CSS',
            'file' => 'assets/public/tourism.css',
            'group' => 'global',
        ],
        'place' => [
            'label' => 'Places to Go CSS',
            'file' => 'assets/public/tourism-place.css',
            'group' => 'content',
        ],
        'activity' => [
            'label' => 'Things to Do CSS',
            'file' => 'assets/public/tourism-activity.css',
            'group' => 'content',
        ],
        'stay' => [
            'label' => 'Places to Stay CSS',
            'file' => 'assets/public/tourism-stay.css',
            'group' => 'content',
        ],
        'operator' => [
            'label' => 'Tour Operators CSS',
            'file' => 'assets/public/tourism-operator.css',
            'group' => 'content',
        ],
        'package' => [
            'label' => 'Packages CSS',
            'file' => 'assets/public/tourism-package.css',
            'group' => 'content',
        ],
        'listing' => [
            'label' => 'Listings CSS',
            'file' => 'assets/public/tourism-listing.css',
            'group' => 'widget',
        ],
        'card' => [
            'label' => 'Cards CSS',
            'file' => 'assets/public/tourism-card.css',
            'group' => 'widget',
        ],
        'controls' => [
            'label' => 'Search, Filters, Sorting, and Pagination CSS',
            'file' => 'assets/public/tourism-controls.css',
            'group' => 'widget',
        ],
        'gallery' => [
            'label' => 'Gallery CSS',
            'file' => 'assets/public/tourism-gallery.css',
            'group' => 'widget',
        ],
        'map' => [
            'label' => 'Map CSS',
            'file' => 'assets/public/tourism-map.css',
            'group' => 'widget',
        ],
        'related' => [
            'label' => 'Related Records CSS',
            'file' => 'assets/public/tourism-related.css',
            'group' => 'widget',
        ],
    ];

    public function __construct(private string $pluginFile) {}

    /** @return array<string, array{label: string, file: string, group: string}> */
    public function scopes(): array
    {
        return self::SCOPES;
    }

    public function css(string $scope): string
    {
        $path = $this->path($scope);

        if ($path === '' || !is_readable($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return is_string($contents) ? $contents : '';
    }

    public function assetUrl(string $scope): string
    {
        $definition = self::SCOPES[$scope] ?? null;

        return is_array($definition)
            ? plugin_dir_url($this->pluginFile) . $definition['file']
            : '';
    }

    private function path(string $scope): string
    {
        $definition = self::SCOPES[$scope] ?? null;

        return is_array($definition) ? plugin_dir_path($this->pluginFile) . $definition['file'] : '';
    }
}
