<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class DocumentationStructureTest extends TestCase
{
    public function testTheMainReadmeIsBriefAndPointsToDetailedDocumentation(): void
    {
        $root = dirname(__DIR__, 2);
        $readme = file_get_contents($root . '/README.md');
        $readmeLines = file($root . '/README.md');

        self::assertIsString($readme);
        self::assertIsArray($readmeLines);
        self::assertLessThanOrEqual(100, count($readmeLines));
        self::assertStringContainsString('[documentation home](docs/README.md)', $readme);
        self::assertStringContainsString('[User guides](docs/user/README.md)', $readme);
        self::assertStringContainsString('[Developer documentation](docs/developer/README.md)', $readme);
        self::assertStringContainsString('[Testing and release checks](docs/testing/README.md)', $readme);
    }

    public function testRequiredDocumentationFilesExist(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([
            'docs/README.md',
            'docs/user/README.md',
            'docs/user/getting-started.md',
            'docs/user/record-workflow.md',
            'docs/user/relationships.md',
            'docs/user/media-and-permalinks.md',
            'docs/user/csv-import-export.md',
            'docs/user/templates-and-builders.md',
            'docs/user/shortcodes.md',
            'docs/user/seo-maps-and-languages.md',
            'docs/user/woocommerce.md',
            'docs/user/maintenance-and-privacy.md',
            'docs/user/troubleshooting.md',
            'docs/developer/README.md',
            'docs/developer/architecture.md',
            'docs/developer/extension-points.md',
            'docs/developer/release-process.md',
            'docs/testing/README.md',
            'docs/testing/acceptance-checklist.md',
            'docs/known-limitations.md',
        ] as $path) {
            self::assertFileExists($root . '/' . $path, $path . ' is missing.');
        }
    }
}
