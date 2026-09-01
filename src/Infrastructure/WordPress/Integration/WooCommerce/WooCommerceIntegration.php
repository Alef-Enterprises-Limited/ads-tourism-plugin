<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

final readonly class WooCommerceIntegration
{
    public function __construct(
        private HposCompatibility $hpos,
        private CommerceSettings $settings,
        private PackageCommerceMetaBox $metaBox,
        private CommerceActionController $actions,
        private PackageProductCleanup $cleanup,
        private CommerceShortcodes $shortcodes,
        private ProductPageTourismContext $productContext,
        private PackageRecordUrlFilter $recordUrls,
    ) {}

    public function register(): void
    {
        $this->hpos->register();
        add_action('admin_menu', [$this->settings, 'registerMenu']);
        add_action('admin_init', [$this->settings, 'registerSettings']);
        add_action('add_meta_boxes', [$this->metaBox, 'register']);
        add_action('save_post', [$this->metaBox, 'save'], 26);
        add_filter('redirect_post_location', [$this->metaBox, 'filterRedirect'], 20, 2);
        add_action('admin_notices', [$this->metaBox, 'renderNotice']);
        add_action('admin_post_' . CommerceActionController::ACTION, [$this->actions, 'handle']);
        add_action('before_delete_post', [$this->cleanup, 'beforeDelete'], 35);
        $this->shortcodes->register();
        $this->productContext->register();
        $this->recordUrls->register();
    }
}
