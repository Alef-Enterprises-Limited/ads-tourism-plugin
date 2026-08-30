<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Permalink\PermalinkBaseValidator;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final readonly class PermalinkSettings
{
    public const OPTION_BASES = 'ads_tourism_permalink_bases';

    public const OPTION_REDIRECTS = 'ads_tourism_permalink_base_redirects';

    private const HIERARCHICAL_PLACES_KEY = 'hierarchical_places';

    public function __construct(private PermalinkBaseValidator $validator) {}

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_BASES, [
            'type' => 'object',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => $this->defaults(),
        ]);
        add_settings_section(
            'ads_tourism_permalink_section',
            __('Permalinks', 'ads-tourism'),
            [$this, 'renderSectionDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_BASES,
            __('Tourism URL bases', 'ads-tourism'),
            [$this, 'renderFields'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_permalink_section',
        );
    }

    /** @return array<string, string|bool> */
    public function sanitize(mixed $value): array
    {
        $submitted = is_array($value) ? $value : [];
        $defaults = $this->defaults();
        $bases = [];

        foreach ($this->baseDefaults() as $key => $default) {
            $bases[$key] = sanitize_title((string) ($submitted[$key] ?? $default));
        }

        $result = $this->validator->validate($bases);

        if (!$result->isValid()) {
            foreach ($result->errors as $index => $error) {
                add_settings_error(
                    self::OPTION_BASES,
                    'ads_tourism_permalink_' . $index,
                    $error,
                    'error',
                );
            }

            $current = get_option(self::OPTION_BASES, $defaults);

            return $this->normalize($current);
        }

        return [
            ...$result->bases,
            self::HIERARCHICAL_PLACES_KEY => rest_sanitize_boolean(
                $submitted[self::HIERARCHICAL_PLACES_KEY] ?? false,
            ),
        ];
    }

    public function contentTypeBase(ContentType $contentType): string
    {
        return (string) $this->get()[$contentType->value];
    }

    public function taxonomyBase(TourismTaxonomy $taxonomy): string
    {
        return (string) $this->get()[$taxonomy->value];
    }

    public function hierarchicalPlaces(): bool
    {
        return (bool) $this->get()[self::HIERARCHICAL_PLACES_KEY];
    }

    /** @return array<string, string|bool> */
    public function get(): array
    {
        return $this->normalize(get_option(self::OPTION_BASES, $this->defaults()));
    }

    public function handleAdded(string $optionName, mixed $value): void
    {
        $this->handleUpdated($this->defaults(), $value);
    }

    public function handleUpdated(mixed $oldValue, mixed $newValue): void
    {
        $oldSettings = $this->normalize($oldValue);
        $newSettings = $this->normalize($newValue);
        $storedRedirects = get_option(self::OPTION_REDIRECTS, []);
        $redirects = [];

        if (is_array($storedRedirects)) {
            foreach ($storedRedirects as $alias => $target) {
                if (is_string($alias) && is_string($target)) {
                    $redirects[$alias] = $target;
                }
            }
        }

        foreach (array_keys($this->baseDefaults()) as $key) {
            $oldBase = (string) $oldSettings[$key];
            $newBase = (string) $newSettings[$key];

            if ($oldBase === $newBase) {
                continue;
            }

            foreach ($redirects as $alias => $target) {
                if ($target === $oldBase) {
                    $redirects[$alias] = $newBase;
                }
            }

            $redirects[$oldBase] = $newBase;
            unset($redirects[$newBase]);
        }

        update_option(self::OPTION_REDIRECTS, $redirects, false);
        flush_rewrite_rules();
    }

    /** @return array<string, string|bool> */
    private function normalize(mixed $settings): array
    {
        $settings = is_array($settings) ? $settings : [];
        $normalized = [];

        foreach ($this->baseDefaults() as $key => $default) {
            $stored = $settings[$key] ?? $default;
            $normalized[$key] = is_string($stored) && $stored !== '' ? $stored : $default;
        }

        $normalized[self::HIERARCHICAL_PLACES_KEY] = rest_sanitize_boolean(
            $settings[self::HIERARCHICAL_PLACES_KEY] ?? false,
        );

        return $normalized;
    }

    public function renderSectionDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Change URL bases carefully. ADS Tourism validates duplicates and reserved paths, then redirects previously configured bases.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderFields(): void
    {
        $settings = $this->get();

        foreach ($this->baseLabels() as $key => $label) {
            $base = (string) $settings[$key];
            echo '<label for="ads-tourism-base-' . esc_attr($key) . '"><strong>' . esc_html($label) . '</strong></label><br>';
            echo '<input class="regular-text" id="ads-tourism-base-' . esc_attr($key) . '" type="text" name="';
            echo esc_attr(self::OPTION_BASES . '[' . $key . ']') . '" value="' . esc_attr($base) . '">';
            echo '<p class="description">' . esc_html(home_url('/' . $base . '/example/')) . '</p>';
        }

        echo '<input type="hidden" name="' . esc_attr(self::OPTION_BASES . '[' . self::HIERARCHICAL_PLACES_KEY . ']') . '" value="0">';
        echo '<label><input type="checkbox" name="';
        echo esc_attr(self::OPTION_BASES . '[' . self::HIERARCHICAL_PLACES_KEY . ']') . '" value="1" ';
        echo checked((bool) $settings[self::HIERARCHICAL_PLACES_KEY], true, false) . '> ';
        echo esc_html__('Include parent-place paths in hierarchical Place URLs.', 'ads-tourism') . '</label>';
    }

    /** @return array<string, string|bool> */
    private function defaults(): array
    {
        return [...$this->baseDefaults(), self::HIERARCHICAL_PLACES_KEY => false];
    }

    /** @return array<string, string> */
    private function baseDefaults(): array
    {
        $defaults = [];

        foreach (ContentType::cases() as $contentType) {
            $defaults[$contentType->value] = $contentType->rewriteBase();
        }

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            $defaults[$taxonomy->value] = $taxonomy->rewriteBase();
        }

        return $defaults;
    }

    /** @return array<string, string> */
    private function baseLabels(): array
    {
        return [
            ContentType::PLACE->value => __('Places to Go', 'ads-tourism'),
            ContentType::ACTIVITY->value => __('Things to Do', 'ads-tourism'),
            ContentType::STAY->value => __('Places to Stay', 'ads-tourism'),
            ContentType::OPERATOR->value => __('Tour Operators', 'ads-tourism'),
            ContentType::PACKAGE->value => __('Packages', 'ads-tourism'),
            TourismTaxonomy::PLACE_TYPE->value => __('Place Types', 'ads-tourism'),
            TourismTaxonomy::ACTIVITY_TYPE->value => __('Activity Types', 'ads-tourism'),
            TourismTaxonomy::STAY_TYPE->value => __('Stay Types', 'ads-tourism'),
            TourismTaxonomy::PACKAGE_TYPE->value => __('Package Types', 'ads-tourism'),
            TourismTaxonomy::AMENITY->value => __('Amenities', 'ads-tourism'),
            TourismTaxonomy::TRAVELLER->value => __('Traveller Types', 'ads-tourism'),
            TourismTaxonomy::ACCESSIBILITY->value => __('Accessibility Features', 'ads-tourism'),
            TourismTaxonomy::TOURISM_TAG->value => __('Tourism Tags', 'ads-tourism'),
            TourismTaxonomy::GEOGRAPHIC_AREA->value => __('Geographic Areas', 'ads-tourism'),
        ];
    }
}
