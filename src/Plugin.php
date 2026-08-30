<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TaxonomyRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TranslationLoader;

final readonly class Plugin
{
    public const SCHEMA_VERSION = '1';

    public const VERSION = '0.1.0';

    public function __construct(
        private ContentTypeRegistrar $contentTypes,
        private TaxonomyRegistrar $taxonomies,
        private AdminMenu $adminMenu,
        private TranslationLoader $translations,
    ) {}

    public function register(): void
    {
        add_action('plugins_loaded', [$this->translations, 'load']);
        add_action('init', [$this, 'registerContentModel']);
        add_action('admin_menu', [$this->adminMenu, 'register']);
    }

    public function registerContentModel(): void
    {
        $this->contentTypes->register();
        $this->taxonomies->register();
    }

    public function activate(): void
    {
        $this->registerContentModel();

        update_option('ads_tourism_version', self::VERSION);
        update_option('ads_tourism_schema_version', self::SCHEMA_VERSION);
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
