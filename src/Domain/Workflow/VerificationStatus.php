<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Workflow;

enum VerificationStatus: string
{
    case UNVERIFIED = 'unverified';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case NEEDS_UPDATE = 'needs_update';
    case REJECTED = 'rejected';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::UNVERIFIED->value => 'Unverified',
            self::PENDING->value => 'Pending verification',
            self::VERIFIED->value => 'Verified',
            self::NEEDS_UPDATE->value => 'Needs update',
            self::REJECTED->value => 'Rejected',
        ];
    }
}
