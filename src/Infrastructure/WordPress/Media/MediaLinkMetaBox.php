<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Application\Media\MediaLinkService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use AlefDigitalSolutions\ADSTourism\Plugin;
use InvalidArgumentException;
use WP_Post;

final readonly class MediaLinkMetaBox
{
    private const NONCE_ACTION = 'ads_tourism_save_media_links';

    private const NONCE_NAME = 'ads_tourism_media_links_nonce';

    public function __construct(
        private MediaLinkService $mediaLinks,
        private string $pluginFile,
    ) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_meta_box(
                'ads-tourism-media-links',
                __('Tourism gallery', 'ads-tourism'),
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

        wp_enqueue_media();
        $baseUrl = plugin_dir_url($this->pluginFile);
        wp_enqueue_style('ads-tourism-media-links', $baseUrl . 'assets/admin/media-links.css', [], Plugin::VERSION);
        wp_enqueue_script('ads-tourism-media-links', $baseUrl . 'assets/admin/media-links.js', [], Plugin::VERSION, true);
        wp_localize_script('ads-tourism-media-links', 'adsTourismMediaLinks', [
            'roles' => MediaRole::labels(),
            'strings' => [
                'chooseImages' => __('Choose gallery images', 'ads-tourism'),
                'useImages' => __('Add selected images', 'ads-tourism'),
                'invalidUrl' => __('Enter a relative path or a valid HTTPS URL.', 'ads-tourism'),
                'attachment' => __('Media Library image', 'ads-tourism'),
                'external' => __('Linked image', 'ads-tourism'),
                'primary' => __('Primary', 'ads-tourism'),
                'remove' => __('Detach', 'ads-tourism'),
                'moveUp' => __('Move up', 'ads-tourism'),
                'moveDown' => __('Move down', 'ads-tourism'),
                'role' => __('Role', 'ads-tourism'),
                'title' => __('Title override', 'ads-tourism'),
                'alt' => __('Alt-text override', 'ads-tourism'),
                'caption' => __('Caption', 'ads-tourism'),
                'credit' => __('Credit', 'ads-tourism'),
                'rights' => __('Rights notice', 'ads-tourism'),
            ],
        ]);
    }

    public function render(WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<input type="hidden" name="ads_tourism_media_present" value="1">';
        echo '<p>';
        echo esc_html__(
            'Select images from the WordPress Media Library or link to an HTTPS or site-relative image. Detaching an image never deletes the Media Library file.',
            'ads-tourism',
        );
        echo '</p>';
        echo '<p><button type="button" class="button ads-tourism-media__choose">';
        echo esc_html__('Choose Media Library images', 'ads-tourism') . '</button></p>';
        echo '<div class="ads-tourism-media__external">';
        echo '<label for="ads-tourism-media-url">' . esc_html__('Linked image URL or relative path', 'ads-tourism') . '</label> ';
        echo '<input id="ads-tourism-media-url" class="regular-text" type="text" placeholder="/media/image.jpg or https://…"> ';
        echo '<button type="button" class="button ads-tourism-media__add-url">';
        echo esc_html__('Add linked image', 'ads-tourism') . '</button>';
        echo '<span class="ads-tourism-media__url-error" role="alert"></span></div>';
        echo '<ol class="ads-tourism-media__list">';

        foreach ($this->mediaLinks->find($post->ID) as $index => $mediaLink) {
            $this->renderItem($mediaLink, $index);
        }

        echo '</ol>';
    }

    public function save(int $postId): void
    {
        if (!$this->requestCanSave($postId) || !isset($_POST['ads_tourism_media_present'])) {
            return;
        }

        $submitted = isset($_POST['ads_tourism_media_links']) && is_array($_POST['ads_tourism_media_links'])
            ? wp_unslash($_POST['ads_tourism_media_links'])
            : [];
        $primaryIndex = isset($_POST['ads_tourism_media_primary'])
            ? absint($_POST['ads_tourism_media_primary'])
            : null;
        $mediaLinks = [];

        foreach ($submitted as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $row['is_primary'] = $primaryIndex !== null && (int) $index === $primaryIndex;

            $mediaLink = $this->mediaLinkFromRequest($postId, $row);

            if ($mediaLink !== null) {
                $mediaLinks[] = $mediaLink;
            }
        }

        try {
            $this->mediaLinks->replace($postId, $mediaLinks);
        } catch (InvalidArgumentException) {
            // Retain the existing gallery when a stale or tampered request fails validation.
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
            && current_user_can('edit_post', $postId)
            && ContentType::tryFrom((string) get_post_type($postId)) !== null;
    }

    /** @param array<array-key, mixed> $row */
    private function mediaLinkFromRequest(int $postId, array $row): ?MediaLink
    {
        $attachmentId = isset($row['attachment_id']) ? absint($row['attachment_id']) : 0;
        $rawUrl = isset($row['media_url']) ? trim((string) $row['media_url']) : '';
        $urlType = isset($row['url_type'])
            ? MediaUrlType::tryFrom(sanitize_key((string) $row['url_type']))
            : null;
        $role = isset($row['media_role'])
            ? MediaRole::tryFrom(sanitize_key((string) $row['media_role']))
            : null;

        if ($attachmentId <= 0 && $rawUrl === '') {
            return null;
        }

        $mediaUrl = $attachmentId > 0 ? null : $this->sanitizeMediaUrl($rawUrl, $urlType);

        try {
            return new MediaLink(
                $postId,
                $attachmentId > 0 ? $attachmentId : null,
                $mediaUrl,
                $attachmentId > 0 ? null : $urlType,
                $role ?? MediaRole::GALLERY,
                sanitize_text_field((string) ($row['custom_title'] ?? '')),
                sanitize_text_field((string) ($row['custom_alt_text'] ?? '')),
                sanitize_textarea_field((string) ($row['custom_caption'] ?? '')),
                sanitize_text_field((string) ($row['credit'] ?? '')),
                sanitize_textarea_field((string) ($row['rights_notice'] ?? '')),
                rest_sanitize_boolean($row['is_primary'] ?? false),
            );
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    private function sanitizeMediaUrl(string $url, ?MediaUrlType $urlType): string
    {
        if ($urlType === MediaUrlType::RELATIVE) {
            return '/' . ltrim(sanitize_text_field($url), '/');
        }

        return esc_url_raw($url, ['https']);
    }

    private function renderItem(MediaLink $mediaLink, int $index): void
    {
        $prefix = 'ads_tourism_media_links[' . $index . ']';
        $previewUrl = $mediaLink->attachmentId !== null
            ? (string) wp_get_attachment_image_url($mediaLink->attachmentId, 'thumbnail')
            : $this->displayUrl((string) $mediaLink->mediaUrl, $mediaLink->urlType);
        echo '<li class="ads-tourism-media__item">';
        echo '<div class="ads-tourism-media__preview">';

        if ($previewUrl !== '') {
            echo '<img src="' . esc_url($previewUrl) . '" alt="">';
        }

        echo '</div><div class="ads-tourism-media__fields">';
        echo '<input type="hidden" data-field="attachment_id" name="' . esc_attr($prefix . '[attachment_id]') . '" value="';
        echo esc_attr((string) ($mediaLink->attachmentId ?? 0)) . '">';
        echo '<input type="hidden" data-field="media_url" name="' . esc_attr($prefix . '[media_url]') . '" value="';
        echo esc_attr((string) $mediaLink->mediaUrl) . '">';
        echo '<input type="hidden" data-field="url_type" name="' . esc_attr($prefix . '[url_type]') . '" value="';
        echo esc_attr((string) $mediaLink->urlType?->value) . '">';
        $this->renderRoleSelect($prefix, $mediaLink->role);
        $this->renderTextInput($prefix, 'custom_title', __('Title override', 'ads-tourism'), $mediaLink->customTitle);
        $this->renderTextInput($prefix, 'custom_alt_text', __('Alt-text override', 'ads-tourism'), $mediaLink->customAltText);
        $this->renderTextInput($prefix, 'custom_caption', __('Caption', 'ads-tourism'), $mediaLink->customCaption);
        $this->renderTextInput($prefix, 'credit', __('Credit', 'ads-tourism'), $mediaLink->credit);
        $this->renderTextInput($prefix, 'rights_notice', __('Rights notice', 'ads-tourism'), $mediaLink->rightsNotice);
        echo '<label><input data-field="is_primary" type="radio" name="ads_tourism_media_primary" value="';
        echo esc_attr((string) $index) . '" ' . checked($mediaLink->isPrimary, true, false) . '> ';
        echo esc_html__('Primary', 'ads-tourism') . '</label>';
        echo '</div><div class="ads-tourism-media__actions">';
        echo '<button type="button" class="button-link ads-tourism-media__up">' . esc_html__('Move up', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button-link ads-tourism-media__down">' . esc_html__('Move down', 'ads-tourism') . '</button> ';
        echo '<button type="button" class="button-link-delete ads-tourism-media__remove">' . esc_html__('Detach', 'ads-tourism') . '</button>';
        echo '</div></li>';
    }

    private function renderRoleSelect(string $prefix, MediaRole $selectedRole): void
    {
        echo '<label>' . esc_html__('Role', 'ads-tourism') . ' <select data-field="media_role" name="';
        echo esc_attr($prefix . '[media_role]') . '">';

        foreach (MediaRole::labels() as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($selectedRole->value, $value, false) . '>';
            echo esc_html($label) . '</option>';
        }

        echo '</select></label>';
    }

    private function renderTextInput(string $prefix, string $field, string $label, string $value): void
    {
        echo '<label>' . esc_html($label) . ' <input class="regular-text" data-field="' . esc_attr($field) . '" type="text" name="';
        echo esc_attr($prefix . '[' . $field . ']') . '" value="' . esc_attr($value) . '"></label>';
    }

    private function displayUrl(string $url, ?MediaUrlType $urlType): string
    {
        return $urlType === MediaUrlType::RELATIVE ? home_url($url) : $url;
    }
}
