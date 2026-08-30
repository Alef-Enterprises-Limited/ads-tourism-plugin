<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Workflow;

use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use PHPUnit\Framework\TestCase;

final class VerificationStatusTest extends TestCase
{
    public function testEveryStatusHasAnAdministrativeLabel(): void
    {
        self::assertSame(
            array_map(
                static fn(VerificationStatus $status): string => $status->value,
                VerificationStatus::cases(),
            ),
            array_keys(VerificationStatus::labels()),
        );
    }
}
