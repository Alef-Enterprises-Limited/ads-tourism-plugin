<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Workflow;

final readonly class PublicationPolicy
{
    public function canPublish(VerificationStatus $status, bool $verificationRequired): bool
    {
        return !$verificationRequired || $status === VerificationStatus::VERIFIED;
    }
}
