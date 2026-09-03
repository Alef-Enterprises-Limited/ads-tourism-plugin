<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\HelpAdminPage;
use PHPUnit\Framework\TestCase;

final class HelpAdminPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['ads_tourism_test_menu_pages'] = [];
    }

    public function testItRegistersAnAccessibleHelpSubmenu(): void
    {
        (new HelpAdminPage())->registerMenu();

        self::assertCount(1, $GLOBALS['ads_tourism_test_menu_pages']);
        self::assertSame(AdminMenu::SLUG, $GLOBALS['ads_tourism_test_menu_pages'][0]['parent']);
        self::assertSame('Help', $GLOBALS['ads_tourism_test_menu_pages'][0]['menu_title']);
        self::assertSame('edit_posts', $GLOBALS['ads_tourism_test_menu_pages'][0]['capability']);
        self::assertSame(HelpAdminPage::SLUG, $GLOBALS['ads_tourism_test_menu_pages'][0]['slug']);
    }

    public function testItDisplaysTheCompleteShortcodeReference(): void
    {
        ob_start();
        (new HelpAdminPage())->render();
        $markup = (string) ob_get_clean();

        foreach ([
            'ads_tourism_places',
            'ads_tourism_activities',
            'ads_tourism_stays',
            'ads_tourism_operators',
            'ads_tourism_packages',
            'ads_tourism_listing',
            'ads_tourism_search',
            'ads_tourism_filters',
            'ads_tourism_sort',
            'ads_tourism_results',
            'ads_tourism_pagination',
            'ads_tourism_field',
            'ads_tourism_gallery',
            'ads_tourism_related_places',
            'ads_tourism_related_activities',
            'ads_tourism_related_stays',
            'ads_tourism_related_operators',
            'ads_tourism_related_packages',
            'ads_tourism_package_itinerary',
            'ads_tourism_package_provider',
            'ads_tourism_map',
            'ads_tourism_commerce_controls',
        ] as $shortcode) {
            self::assertStringContainsString($shortcode, $markup);
        }

        self::assertStringContainsString('context="discover"', $markup);
        self::assertStringContainsString('Administration references', $markup);
        self::assertStringContainsString('Documentation and guides', $markup);
        self::assertStringContainsString(
            'https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/blob/main/docs/README.md',
            $markup,
        );
        self::assertStringContainsString(
            'https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/blob/main/docs/user/getting-started.md',
            $markup,
        );
        self::assertStringContainsString(
            'https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/blob/main/docs/user/relationships.md',
            $markup,
        );
        self::assertStringContainsString(
            'https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/blob/main/docs/user/troubleshooting.md',
            $markup,
        );
    }
}
