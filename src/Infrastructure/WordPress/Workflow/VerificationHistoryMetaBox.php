<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use WP_Post;

final class VerificationHistoryMetaBox
{
    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_meta_box(
                'ads-tourism-verification-history',
                __('Verification history', 'ads-tourism'),
                [$this, 'render'],
                $contentType->value,
                'normal',
                'low',
            );
        }
    }

    public function render(WP_Post $post): void
    {
        $history = get_post_meta($post->ID, VerificationHistoryService::HISTORY_KEY, true);

        if (!is_array($history) || $history === []) {
            echo '<p>' . esc_html__('No verification changes have been recorded yet.', 'ads-tourism') . '</p>';

            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Status', 'ads-tourism') . '</th>';
        echo '<th>' . esc_html__('Changed at', 'ads-tourism') . '</th>';
        echo '<th>' . esc_html__('User ID', 'ads-tourism') . '</th>';
        echo '<th>' . esc_html__('Source', 'ads-tourism') . '</th>';
        echo '<th>' . esc_html__('Note', 'ads-tourism') . '</th>';
        echo '</tr></thead><tbody>';

        foreach (array_reverse($history) as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $status = VerificationStatus::tryFrom((string) ($entry['status'] ?? ''));
            $statusLabel = $status === null
                ? (string) ($entry['status'] ?? '')
                : __(VerificationStatus::labels()[$status->value], 'ads-tourism');
            $source = trim(implode(' — ', array_filter([
                (string) ($entry['source_name'] ?? ''),
                (string) ($entry['source_reference'] ?? ''),
            ])));

            echo '<tr><td>' . esc_html($statusLabel) . '</td>';
            echo '<td>' . esc_html((string) ($entry['changed_at'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($entry['user_id'] ?? '')) . '</td>';
            echo '<td>' . ($source === '' ? '—' : esc_html($source)) . '</td>';
            echo '<td>' . esc_html((string) ($entry['note'] ?? '')) . '</td></tr>';
        }

        echo '</tbody></table>';
    }
}
