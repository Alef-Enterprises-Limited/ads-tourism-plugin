<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use PHPUnit\Framework\TestCase;

final class AdminMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['ads_tourism_test_menu_pages'] = [];
    }

    public function testItRegistersTheTagsAndCategoriesSubmenu(): void
    {
        (new AdminMenu())->register();

        $submenus = array_values(array_filter(
            $GLOBALS['ads_tourism_test_menu_pages'],
            static fn(array $menu): bool => $menu['type'] === 'submenu',
        ));

        self::assertCount(1, $submenus);
        self::assertSame('ads-tourism', $submenus[0]['parent']);
        self::assertSame('Tags & Categories', $submenus[0]['menu_title']);
        self::assertSame('manage_categories', $submenus[0]['capability']);
        self::assertSame(AdminMenu::TAXONOMY_SLUG, $submenus[0]['slug']);
    }

    public function testTheDashboardLinksToEveryTaxonomyManagementScreen(): void
    {
        ob_start();
        (new AdminMenu())->renderTaxonomyDashboard();
        $markup = (string) ob_get_clean();

        foreach ([
            'ads_place_type|ads_place',
            'ads_activity_type|ads_activity',
            'ads_stay_type|ads_stay',
            'ads_package_type|ads_package',
            'ads_amenity|ads_stay',
            'ads_traveller|ads_place',
            'ads_accessibility|ads_place',
            'ads_tourism_tag|ads_place',
            'ads_geo_area|ads_place',
        ] as $taxonomy) {
            [$taxonomyKey, $postType] = explode('|', $taxonomy, 2);

            self::assertStringContainsString(
                'edit-tags.php?taxonomy=' . $taxonomyKey . '&post_type=' . $postType,
                $markup,
            );
        }
    }
}
