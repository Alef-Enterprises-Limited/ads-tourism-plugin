<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\PublicationPolicy;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;

final class PublishingGate
{
    private bool $publicationBlocked = false;

    public function __construct(private readonly PublicationPolicy $policy) {}

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $postArguments
     *
     * @return array<string, mixed>
     */
    public function filterPostData(array $data, array $postArguments): array
    {
        $contentType = ContentType::tryFrom((string) ($data['post_type'] ?? ''));

        if ($contentType === null || ($data['post_status'] ?? '') !== 'publish') {
            return $data;
        }

        $verificationRequired = (bool) get_option(WorkflowSettings::OPTION_REQUIRE_VERIFICATION, true);

        if ($this->policy->canPublish($this->verificationStatus($postArguments), $verificationRequired)) {
            return $data;
        }

        $data['post_status'] = 'pending';
        $this->publicationBlocked = true;

        return $data;
    }

    public function filterRedirect(string $location): string
    {
        if (!$this->publicationBlocked) {
            return $location;
        }

        return add_query_arg('ads_tourism_publish_blocked', '1', $location);
    }

    public function renderNotice(): void
    {
        if (!isset($_GET['ads_tourism_publish_blocked'])) {
            return;
        }

        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__(
            'ADS Tourism kept this record in review because it must be verified before publication.',
            'ads-tourism',
        );
        echo '</p></div>';
    }

    /**
     * @param array<string, mixed> $postArguments
     */
    private function verificationStatus(array $postArguments): VerificationStatus
    {
        $metaInput = isset($postArguments['meta_input']) && is_array($postArguments['meta_input'])
            ? $postArguments['meta_input']
            : [];
        $submittedFields = isset($_POST['ads_tourism_fields']) && is_array($_POST['ads_tourism_fields'])
            ? wp_unslash($_POST['ads_tourism_fields'])
            : [];
        $submittedStatus = $metaInput[VerificationHistoryService::STATUS_KEY]
            ?? $submittedFields[VerificationHistoryService::STATUS_KEY]
            ?? null;

        if (is_string($submittedStatus)) {
            return VerificationStatus::tryFrom(sanitize_key($submittedStatus))
                ?? VerificationStatus::UNVERIFIED;
        }

        $postId = isset($postArguments['ID']) ? absint($postArguments['ID']) : 0;

        return VerificationStatus::tryFrom(
            (string) get_post_meta($postId, VerificationHistoryService::STATUS_KEY, true),
        ) ?? VerificationStatus::UNVERIFIED;
    }
}
