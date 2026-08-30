<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Workflow;

use AlefDigitalSolutions\ADSTourism\Domain\Workflow\PublicationPolicy;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use PHPUnit\Framework\TestCase;

final class PublicationPolicyTest extends TestCase
{
    private PublicationPolicy $policy;

    protected function setUp(): void
    {
        $this->policy = new PublicationPolicy();
    }

    public function testOnlyVerifiedRecordsPassTheDefaultPublicationGate(): void
    {
        foreach (VerificationStatus::cases() as $status) {
            self::assertSame(
                $status === VerificationStatus::VERIFIED,
                $this->policy->canPublish($status, true),
                $status->value,
            );
        }
    }

    public function testAdministratorsMayDisableThePublicationGate(): void
    {
        self::assertTrue($this->policy->canPublish(VerificationStatus::UNVERIFIED, false));
    }
}
