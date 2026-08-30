<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Plugin;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase
{
    public function testPluginVersionUsesSemanticVersioning(): void
    {
        self::assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/',
            Plugin::VERSION,
        );
    }
}
