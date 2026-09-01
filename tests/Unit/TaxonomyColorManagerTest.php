<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TaxonomyColorManager;
use PHPUnit\Framework\TestCase;

final class TaxonomyColorManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_POST = [];
        $GLOBALS['ads_tourism_test_term_meta'] = [];
        $GLOBALS['ads_tourism_test_registered_term_meta'] = [];
    }

    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

    public function testItAcceptsHexRgbValuesAndLeavesInvalidValuesBlank(): void
    {
        $manager = new TaxonomyColorManager();

        self::assertSame('#12aBc3', $manager->sanitizeColor('#12aBc3'));
        self::assertSame('#fff', $manager->sanitizeColor('#fff'));
        self::assertSame('', $manager->sanitizeColor('rgb(18, 171, 195)'));
        self::assertSame('', $manager->sanitizeColor(null));
    }

    public function testItRegistersColorMetadataForEveryTourismTaxonomy(): void
    {
        (new TaxonomyColorManager())->registerMeta();

        self::assertCount(count(TourismTaxonomy::cases()), $GLOBALS['ads_tourism_test_registered_term_meta']);
        self::assertSame(
            array_map(static fn(TourismTaxonomy $taxonomy): string => $taxonomy->value, TourismTaxonomy::cases()),
            array_column($GLOBALS['ads_tourism_test_registered_term_meta'], 'taxonomy'),
        );

        foreach ($GLOBALS['ads_tourism_test_registered_term_meta'] as $registration) {
            self::assertSame(TaxonomyColorManager::META_KEY, $registration['meta_key']);
            self::assertTrue($registration['arguments']['show_in_rest']);
        }
    }

    public function testItStoresAndClearsColorsOnlyForPluginTaxonomies(): void
    {
        $manager = new TaxonomyColorManager();
        $_POST = [
            TaxonomyColorManager::META_KEY => '#12aBc3',
            'ads_tourism_term_color_nonce' => 'valid',
        ];

        $manager->save(17, 23, 'ads_place_type');

        self::assertSame('#12aBc3', $GLOBALS['ads_tourism_test_term_meta'][17][TaxonomyColorManager::META_KEY]);

        $_POST[TaxonomyColorManager::META_KEY] = '';
        $manager->save(17, 23, 'ads_place_type');

        self::assertArrayNotHasKey(17, $GLOBALS['ads_tourism_test_term_meta']);

        $_POST[TaxonomyColorManager::META_KEY] = '#ffffff';
        $manager->save(17, 23, 'category');

        self::assertArrayNotHasKey(17, $GLOBALS['ads_tourism_test_term_meta']);
    }

    public function testItRendersTheColorFieldWithNoDefaultColor(): void
    {
        ob_start();
        (new TaxonomyColorManager())->renderAddField();
        $markup = (string) ob_get_clean();

        self::assertStringContainsString('class="ads-tourism-term-color"', $markup);
        self::assertStringContainsString('value=""', $markup);
        self::assertStringContainsString('data-default-color=""', $markup);
        self::assertStringContainsString('Leave blank for no color.', $markup);
    }
}
