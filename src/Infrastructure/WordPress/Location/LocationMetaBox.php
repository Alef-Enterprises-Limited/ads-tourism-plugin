<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Location;

use AlefDigitalSolutions\ADSTourism\Application\Location\LocationService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use AlefDigitalSolutions\ADSTourism\Plugin;
use InvalidArgumentException;
use WP_Post;

final readonly class LocationMetaBox
{
    private const NONCE_ACTION = 'ads_tourism_save_locations';

    private const NONCE_NAME = 'ads_tourism_locations_nonce';

    private const PRESENT_FIELD = 'ads_tourism_locations_present';

    public function __construct(
        private LocationService $locations,
        private string $pluginFile,
    ) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_meta_box(
                'ads-tourism-locations',
                __('Tourism locations', 'ads-tourism'),
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
        wp_enqueue_style('ads-tourism-locations', $baseUrl . 'assets/admin/locations.css', [], Plugin::VERSION);
        wp_enqueue_script(
            'ads-tourism-locations',
            $baseUrl . 'assets/admin/locations.js',
            [],
            Plugin::VERSION,
            true,
        );
        wp_localize_script('ads-tourism-locations', 'adsTourismLocations', [
            'roles' => array_map(
                static fn(string $label): string => __($label, 'ads-tourism'),
                LocationRole::labels(),
            ),
            'strings' => [
                'label' => __('Location label', 'ads-tourism'),
                'role' => __('Role', 'ads-tourism'),
                'latitude' => __('Latitude', 'ads-tourism'),
                'longitude' => __('Longitude', 'ads-tourism'),
                'sortOrder' => __('Display order', 'ads-tourism'),
                'primary' => __('Primary location', 'ads-tourism'),
                'showOnMap' => __('Show on maps', 'ads-tourism'),
                'remove' => __('Remove location', 'ads-tourism'),
            ],
        ]);
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<input type="hidden" name="' . esc_attr(self::PRESENT_FIELD) . '" value="1">';
        echo '<p>' . esc_html__(
            'Add one or more valid GPS locations for this record. The first primary location is used by default on maps.',
            'ads-tourism',
        ) . '</p>';
        echo '<ol class="ads-tourism-locations__list" data-next-index="0">';

        $locations = $this->locations->find($post->ID);

        if ($locations === []) {
            $legacy = $this->legacyLocation($post);

            if ($legacy !== null) {
                $locations[] = $legacy;
            }
        }

        foreach ($locations as $index => $location) {
            $this->renderItem($location, $index);
        }

        echo '</ol>';
        echo '<p><button type="button" class="button ads-tourism-locations__add">';
        echo esc_html__('Add location', 'ads-tourism') . '</button></p>';
    }

    public function save(int $postId): void
    {
        if (!$this->requestCanSave($postId) || !isset($_POST[self::PRESENT_FIELD])) {
            return;
        }

        $submitted = isset($_POST['ads_tourism_locations']) && is_array($_POST['ads_tourism_locations'])
            ? wp_unslash($_POST['ads_tourism_locations'])
            : [];
        $locations = [];

        foreach ($submitted as $row) {
            if (!is_array($row)) {
                continue;
            }

            try {
                $location = $this->locationFromRequest($postId, $row);
            } catch (InvalidArgumentException) {
                return;
            }

            if ($location !== null) {
                $locations[] = $location;
            }
        }

        try {
            $this->locations->replace($postId, $locations);
        } catch (InvalidArgumentException) {
            // Retain existing locations when a stale or tampered request fails validation.
        }
    }

    private function requestCanSave(int $postId): bool
    {
        if (
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || wp_is_post_revision($postId) !== false
        ) {
            return false;
        }

        $nonce = isset($_POST[self::NONCE_NAME])
            ? sanitize_text_field((string) wp_unslash($_POST[self::NONCE_NAME]))
            : '';

        return $nonce !== ''
            && wp_verify_nonce($nonce, self::NONCE_ACTION) !== false
            && current_user_can('edit_post', $postId)
            && ContentType::tryFrom((string) get_post_type($postId)) !== null;
    }

    /** @param array<array-key, mixed> $row */
    private function locationFromRequest(int $postId, array $row): ?LocationPoint
    {
        $latitude = $this->scalar($row['latitude'] ?? '');
        $longitude = $this->scalar($row['longitude'] ?? '');

        if ($latitude === '' && $longitude === '') {
            return null;
        }

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new InvalidArgumentException('A location requires numeric latitude and longitude.');
        }

        $role = LocationRole::tryFrom(sanitize_key($this->scalar($row['role'] ?? 'primary')));

        if ($role === null) {
            throw new InvalidArgumentException('A location requires a valid role.');
        }

        return new LocationPoint(
            $postId,
            new Coordinates((float) $latitude, (float) $longitude),
            sanitize_text_field($this->scalar($row['label'] ?? '')),
            $role,
            rest_sanitize_boolean($row['is_primary'] ?? false),
            rest_sanitize_boolean($row['show_on_map'] ?? false),
            max(0, (int) $this->scalar($row['sort_order'] ?? 0)),
        );
    }

    private function renderItem(LocationPoint $location, int $index): void
    {
        $prefix = 'ads_tourism_locations[' . $index . ']';
        echo '<li class="ads-tourism-locations__item">';
        echo '<div class="ads-tourism-locations__fields">';
        $this->textInput($prefix, 'label', __('Location label', 'ads-tourism'), $location->label);
        echo '<label>' . esc_html__('Role', 'ads-tourism') . ' <select name="' . esc_attr($prefix . '[role]') . '">';

        foreach (LocationRole::labels() as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($location->role->value, $value, false) . '>';
            echo esc_html(__($label, 'ads-tourism')) . '</option>';
        }

        echo '</select></label>';
        $this->textInput($prefix, 'latitude', __('Latitude', 'ads-tourism'), (string) $location->coordinates->latitude, 'number');
        $this->textInput($prefix, 'longitude', __('Longitude', 'ads-tourism'), (string) $location->coordinates->longitude, 'number');
        $this->textInput($prefix, 'sort_order', __('Display order', 'ads-tourism'), (string) $location->sortOrder, 'number');
        echo '<label><input type="checkbox" name="' . esc_attr($prefix . '[is_primary]') . '" value="1" '
            . checked($location->isPrimary, true, false) . '> ' . esc_html__('Primary location', 'ads-tourism') . '</label>';
        echo '<label><input type="checkbox" name="' . esc_attr($prefix . '[show_on_map]') . '" value="1" '
            . checked($location->showOnMap, true, false) . '> ' . esc_html__('Show on maps', 'ads-tourism') . '</label>';
        echo '</div><button type="button" class="button-link-delete ads-tourism-locations__remove">';
        echo esc_html__('Remove location', 'ads-tourism') . '</button></li>';
    }

    private function textInput(string $prefix, string $field, string $label, string $value, string $type = 'text'): void
    {
        echo '<label>' . esc_html($label) . ' <input class="regular-text" type="' . esc_attr($type) . '"';
        echo $type === 'number' ? ' step="any"' : '';
        echo ' name="' . esc_attr($prefix . '[' . $field . ']') . '" value="' . esc_attr($value) . '"></label>';
    }

    private function legacyLocation(WP_Post $post): ?LocationPoint
    {
        $prefix = $post->post_type === ContentType::PACKAGE->value ? 'meeting_point_' : '';
        $latitude = get_post_meta($post->ID, 'ads_tourism_' . $prefix . 'latitude', true);
        $longitude = get_post_meta($post->ID, 'ads_tourism_' . $prefix . 'longitude', true);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        try {
            return new LocationPoint(
                $post->ID,
                new Coordinates((float) $latitude, (float) $longitude),
                $prefix === '' ? __('Primary location', 'ads-tourism') : __('Meeting point', 'ads-tourism'),
                $prefix === '' ? LocationRole::PRIMARY : LocationRole::MEETING_POINT,
                true,
                true,
            );
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    private function scalar(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
