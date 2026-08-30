<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

final readonly class TranslationLoader
{
    public function __construct(private string $pluginFile) {}

    public function load(): void
    {
        load_plugin_textdomain(
            'ads-tourism',
            false,
            dirname(plugin_basename($this->pluginFile)) . '/languages',
        );
    }
}
