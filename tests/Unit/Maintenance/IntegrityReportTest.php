<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Maintenance;

use AlefDigitalSolutions\ADSTourism\Application\Maintenance\IntegrityRepairResult;
use AlefDigitalSolutions\ADSTourism\Application\Maintenance\IntegrityReport;
use PHPUnit\Framework\TestCase;

final class IntegrityReportTest extends TestCase
{
    public function testItSummarizesEveryIntegrityCategory(): void
    {
        $report = new IntegrityReport(1, 2, 3, 4, 5);

        self::assertSame(15, $report->issueCount());
        self::assertFalse($report->isHealthy());
    }

    public function testAnEmptyReportIsHealthy(): void
    {
        self::assertTrue((new IntegrityReport(0, 0, 0, 0, 0))->isHealthy());
    }

    public function testRepairTotalsExcludeManualDuplicateResolution(): void
    {
        $result = new IntegrityRepairResult(2, 3, 4, 5);

        self::assertSame(14, $result->repairedCount());
    }
}
