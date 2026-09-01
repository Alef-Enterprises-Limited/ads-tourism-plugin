<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Release;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\ImportRunTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MediaLinkTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MigrationRunner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\RelationshipTableMigration;
use PHPUnit\Framework\TestCase;
use wpdb;

final class MigrationContractTest extends TestCase
{
    public function testFreshInstallMigrationIsIdempotentAndReportsSuccess(): void
    {
        $database = new wpdb();
        $runner = new MigrationRunner(
            new RelationshipTableMigration($database),
            new MediaLinkTableMigration($database),
            new ImportRunTableMigration($database),
        );

        self::assertTrue($runner->run());
        self::assertTrue($runner->run());
    }

    public function testMigrationSourceContainsRecoveryAndNonSensitiveFailureState(): void
    {
        $source = file_get_contents(
            dirname(__DIR__, 3) . '/src/Infrastructure/WordPress/Database/MigrationRunner.php',
        );

        self::assertIsString($source);
        self::assertStringContainsString('acquireLock()', $source);
        self::assertStringContainsString("'exception_type' => \$exception::class", $source);
        self::assertStringNotContainsString("\$exception->getMessage()", $source);
        self::assertStringContainsString('Retry database update', $source);
    }
}
