<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Workflow;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;

final class VerificationHistoryService
{
    public const HISTORY_KEY = '_ads_tourism_verification_history';

    public const STATUS_KEY = 'ads_tourism_verification_status';

    public function recordCurrentState(int $postId): void
    {
        if (wp_is_post_revision($postId) !== false) {
            return;
        }

        if (ContentType::tryFrom((string) get_post_type($postId)) === null) {
            return;
        }

        $status = VerificationStatus::tryFrom((string) get_post_meta($postId, self::STATUS_KEY, true))
            ?? VerificationStatus::UNVERIFIED;
        $history = get_post_meta($postId, self::HISTORY_KEY, true);
        $history = is_array($history) ? $history : [];
        $latestEntry = $history === [] ? null : end($history);
        $latestStatus = is_array($latestEntry) && isset($latestEntry['status'])
            ? (string) $latestEntry['status']
            : null;

        if ($latestStatus === $status->value) {
            return;
        }

        $changedAt = gmdate('Y-m-d H:i:s');
        $userId = get_current_user_id();
        $history[] = [
            'status' => $status->value,
            'changed_at' => $changedAt,
            'user_id' => $userId,
            'source_name' => sanitize_text_field((string) get_post_meta($postId, 'ads_tourism_source_name', true)),
            'source_reference' => sanitize_text_field((string) get_post_meta($postId, 'ads_tourism_source_reference', true)),
            'note' => sanitize_textarea_field((string) get_post_meta($postId, 'ads_tourism_verification_notes', true)),
        ];

        update_post_meta($postId, self::HISTORY_KEY, $history);

        if ($status === VerificationStatus::VERIFIED) {
            update_post_meta($postId, 'ads_tourism_last_verified_at', $changedAt);
            update_post_meta($postId, 'ads_tourism_verified_by_user_id', $userId);
        }
    }
}
