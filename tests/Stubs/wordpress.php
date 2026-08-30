<?php

declare(strict_types=1);

/**
 * Minimal WordPress declarations used only by PHPStan.
 *
 * Runtime code always uses the real WordPress functions.
 */

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function add_action(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}

function add_menu_page(
    string $pageTitle,
    string $menuTitle,
    string $capability,
    string $menuSlug,
    ?callable $callback = null,
    string $iconUrl = '',
    int|float|null $position = null,
): string {
    return '';
}

function current_user_can(string $capability, mixed ...$args): bool
{
    return true;
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return $text;
}

function flush_rewrite_rules(bool $hard = true): void {}

function load_plugin_textdomain(string $domain, bool $deprecated = false, string $pluginRelativePath = ''): bool
{
    return true;
}

function plugin_basename(string $file): string
{
    return $file;
}

/**
 * @param array<string, mixed> $arguments
 */
function register_post_type(string $postType, array $arguments = []): object
{
    return new stdClass();
}

/**
 * @param list<string>         $objectTypes
 * @param array<string, mixed> $arguments
 */
function register_taxonomy(string $taxonomy, array $objectTypes, array $arguments = []): object
{
    return new stdClass();
}

function register_activation_hook(string $file, callable $callback): void {}

function register_deactivation_hook(string $file, callable $callback): void {}

function update_option(string $option, mixed $value, ?bool $autoload = null): bool
{
    return true;
}

function wp_die(string $message = ''): never
{
    throw new RuntimeException($message);
}
