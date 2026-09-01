<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Release;

use PHPUnit\Framework\TestCase;

final class UninstallContractTest extends TestCase
{
    public function testUninstallPreservesSharedWordPressAndWooCommerceRecords(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/uninstall.php');

        self::assertIsString($source);
        self::assertStringContainsString("if (!\$deleteData)", $source);
        self::assertStringContainsString("wp_delete_post(absint(\$postId), true)", $source);
        self::assertStringNotContainsString("wp_delete_attachment", $source);
        self::assertStringNotContainsString("wp_delete_post(\$product", $source);
        self::assertStringContainsString("'_ads_tourism_package_id'", $source);
    }
}
