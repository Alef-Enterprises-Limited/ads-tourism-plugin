<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TaxonomyRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TranslationLoader;

final class PluginFactory
{
    public static function create(string $pluginFile): Plugin
    {
        return new Plugin(
            new ContentTypeRegistrar(),
            new TaxonomyRegistrar(),
            new AdminMenu(),
            new TranslationLoader($pluginFile),
        );
    }
}
