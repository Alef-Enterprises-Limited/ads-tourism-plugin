<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class MediaSettings
{
    public const OPTION_DEFAULT_IMAGES = 'ads_tourism_default_images';

    public function __construct(private string $pluginFile) {}

    public function registerSettings(): void
    {
        register_setting(WorkflowSettings::SETTINGS_GROUP, self::OPTION_DEFAULT_IMAGES, [
            'type' => 'object',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => $this->defaults(),
        ]);
        add_settings_section(
            'ads_tourism_media_defaults_section',
            __('Default images', 'ads-tourism'),
            [$this, 'renderSectionDescription'],
            WorkflowSettings::PAGE_SLUG,
        );
        add_settings_field(
            self::OPTION_DEFAULT_IMAGES,
            __('Fallback images', 'ads-tourism'),
            [$this, 'renderFields'],
            WorkflowSettings::PAGE_SLUG,
            'ads_tourism_media_defaults_section',
        );
    }

    public function enqueueAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== 'ads-tourism_page_' . WorkflowSettings::PAGE_SLUG) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'ads-tourism-media-defaults',
            plugin_dir_url($this->pluginFile) . 'assets/admin/media-defaults.js',
            [],
            Plugin::VERSION,
            true,
        );
        wp_localize_script('ads-tourism-media-defaults', 'adsTourismMediaDefaults', [
            'title' => __('Choose a default image', 'ads-tourism'),
            'button' => __('Use this image', 'ads-tourism'),
        ]);
    }

    /** @return array<string, int> */
    public function sanitize(mixed $value): array
    {
        $submitted = is_array($value) ? $value : [];
        $sanitized = [];

        foreach (array_keys($this->defaults()) as $key) {
            $attachmentId = isset($submitted[$key]) ? absint($submitted[$key]) : 0;
            $sanitized[$key] = $attachmentId > 0 && get_post_type($attachmentId) === 'attachment'
                ? $attachmentId
                : 0;
        }

        return $sanitized;
    }

    public function renderSectionDescription(): void
    {
        echo '<p>';
        echo esc_html__(
            'Fallback images are display choices only. They are not written into a record as its featured image.',
            'ads-tourism',
        );
        echo '</p>';
    }

    public function renderFields(): void
    {
        $settings = $this->get();
        $this->renderImageField('global', __('Global tourism default', 'ads-tourism'), $settings['global']);

        foreach (ContentType::cases() as $contentType) {
            $this->renderImageField(
                $contentType->value,
                sprintf(__('%s default', 'ads-tourism'), $this->contentTypeLabel($contentType)),
                $settings[$contentType->value],
            );
        }
    }

    /** @return array<string, int> */
    public function get(): array
    {
        $settings = get_option(self::OPTION_DEFAULT_IMAGES, $this->defaults());
        $settings = is_array($settings) ? $settings : [];
        $normalized = [];

        foreach ($this->defaults() as $key => $default) {
            $normalized[$key] = isset($settings[$key]) ? absint($settings[$key]) : $default;
        }

        return $normalized;
    }

    /** @return array<string, int> */
    private function defaults(): array
    {
        $defaults = ['global' => 0];

        foreach (ContentType::cases() as $contentType) {
            $defaults[$contentType->value] = 0;
        }

        return $defaults;
    }

    private function renderImageField(string $key, string $label, int $attachmentId): void
    {
        $fieldId = 'ads-tourism-default-image-' . $key;
        $preview = $attachmentId > 0 ? (string) wp_get_attachment_image_url($attachmentId, 'thumbnail') : '';
        echo '<div class="ads-tourism-default-image" style="margin-bottom:1rem">';
        echo '<strong>' . esc_html($label) . '</strong><br>';

        if ($preview !== '') {
            echo '<img data-preview src="' . esc_url($preview) . '" alt="" style="display:block;max-width:120px;height:auto;margin:.4rem 0">';
        } else {
            echo '<img data-preview src="" alt="" style="display:none;max-width:120px;height:auto;margin:.4rem 0">';
        }

        echo '<input id="' . esc_attr($fieldId) . '" data-attachment-id type="hidden" name="';
        echo esc_attr(self::OPTION_DEFAULT_IMAGES . '[' . $key . ']') . '" value="';
        echo esc_attr((string) $attachmentId) . '">';
        echo '<button type="button" class="button" data-choose-default-image>';
        echo esc_html__('Choose image', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button-link-delete" data-clear-default-image>';
        echo esc_html__('Clear', 'ads-tourism') . '</button></div>';
    }

    private function contentTypeLabel(ContentType $contentType): string
    {
        return match ($contentType) {
            ContentType::PLACE => __('Places to Go', 'ads-tourism'),
            ContentType::ACTIVITY => __('Things to Do', 'ads-tourism'),
            ContentType::STAY => __('Places to Stay', 'ads-tourism'),
            ContentType::OPERATOR => __('Tour Operators', 'ads-tourism'),
            ContentType::PACKAGE => __('Packages', 'ads-tourism'),
        };
    }
}
