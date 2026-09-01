<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use WP_Query;

final class WorkflowColumns
{
    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            add_filter(
                'manage_' . $contentType->value . '_posts_columns',
                [$this, 'addColumns'],
            );
            add_action(
                'manage_' . $contentType->value . '_posts_custom_column',
                [$this, 'renderColumn'],
                10,
                2,
            );
        }
    }

    /**
     * @param array<string, string> $columns
     *
     * @return array<string, string>
     */
    public function addColumns(array $columns): array
    {
        $columns['ads_tourism_stage'] = __('Workflow stage', 'ads-tourism');
        $columns['ads_tourism_verification'] = __('Verification', 'ads-tourism');
        $columns['ads_tourism_last_verified'] = __('Last verified', 'ads-tourism');
        $columns['ads_tourism_source'] = __('Source', 'ads-tourism');

        return $columns;
    }

    public function renderColumn(string $column, int $postId): void
    {
        $verification = VerificationStatus::tryFrom(
            (string) get_post_meta($postId, VerificationHistoryService::STATUS_KEY, true),
        ) ?? VerificationStatus::UNVERIFIED;

        if ($column === 'ads_tourism_stage') {
            echo esc_html($this->stageLabel((string) get_post_status($postId), $verification));
        } elseif ($column === 'ads_tourism_verification') {
            echo esc_html(VerificationStatus::labels()[$verification->value]);
        } elseif ($column === 'ads_tourism_last_verified') {
            $lastVerified = (string) get_post_meta($postId, 'ads_tourism_last_verified_at', true);
            echo $lastVerified === '' ? '—' : esc_html($lastVerified);
        } elseif ($column === 'ads_tourism_source') {
            $source = (string) get_post_meta($postId, 'ads_tourism_source_name', true);
            echo $source === '' ? '—' : esc_html($source);
        }
    }

    public function renderFilter(string $postType): void
    {
        if (ContentType::tryFrom($postType) === null) {
            return;
        }

        $selectedStatus = isset($_GET['ads_tourism_verification_filter'])
            ? sanitize_key((string) wp_unslash($_GET['ads_tourism_verification_filter']))
            : '';
        echo '<label class="screen-reader-text" for="ads-tourism-verification-filter">';
        echo esc_html__('Filter by verification status', 'ads-tourism') . '</label>';
        echo '<select id="ads-tourism-verification-filter" name="ads_tourism_verification_filter">';
        echo '<option value="">' . esc_html__('All verification statuses', 'ads-tourism') . '</option>';

        foreach (VerificationStatus::labels() as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($selectedStatus, $value, false) . '>';
            echo esc_html($label) . '</option>';
        }

        echo '</select>';
    }

    public function applyFilter(WP_Query $query): void
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $postType = $query->get('post_type');
        $verification = isset($_GET['ads_tourism_verification_filter'])
            ? VerificationStatus::tryFrom(
                sanitize_key((string) wp_unslash($_GET['ads_tourism_verification_filter'])),
            )
            : null;

        if (!is_string($postType) || ContentType::tryFrom($postType) === null || $verification === null) {
            return;
        }

        $query->set('meta_query', [[
            'key' => VerificationHistoryService::STATUS_KEY,
            'value' => $verification->value,
            'compare' => '=',
        ]]);
    }

    private function stageLabel(string $postStatus, VerificationStatus $verification): string
    {
        return match (true) {
            $postStatus === 'publish' && $verification === VerificationStatus::VERIFIED => 'Published',
            $postStatus === 'pending' && $verification === VerificationStatus::VERIFIED => 'Verified',
            $postStatus === 'pending' => 'In Review',
            default => 'Draft',
        };
    }
}
