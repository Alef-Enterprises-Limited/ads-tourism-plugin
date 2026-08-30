<?php

declare(strict_types=1);

/**
 * Minimal WordPress declarations used only by PHPStan.
 *
 * Runtime code always uses the real WordPress functions and classes.
 */

define('ABSPATH', '/');
define('MINUTE_IN_SECONDS', 60);

class WP_Post
{
    public int $ID = 0;

    public string $post_type = '';

    public string $post_status = '';
}

class WP_Query
{
    /** @var list<WP_Post> */
    public array $posts = [];

    public int $max_num_pages = 0;

    /** @param array<string, mixed> $query */
    public function __construct(array $query = []) {}

    public function is_main_query(): bool
    {
        return true;
    }

    public function get(string $queryVariable): mixed
    {
        return null;
    }

    public function set(string $queryVariable, mixed $value): void {}
}

class WP_Screen
{
    public string $post_type = '';
}

class wpdb
{
    public string $prefix = 'wp_';

    public string $posts = 'wp_posts';

    public int $insert_id = 0;

    public function get_charset_collate(): string
    {
        return '';
    }

    public function prepare(string $query, mixed ...$arguments): string
    {
        return $query;
    }

    public function query(string $query): int|false
    {
        return 0;
    }

    /** @return list<object>|null */
    public function get_results(string $query): ?array
    {
        return [];
    }
}

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function absint(mixed $value): int
{
    return abs((int) $value);
}

function add_action(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}

function add_filter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}

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

function add_submenu_page(
    string $parentSlug,
    string $pageTitle,
    string $menuTitle,
    string $capability,
    string $menuSlug,
    ?callable $callback = null,
    int|float|null $position = null,
): string|false {
    return '';
}

function add_meta_box(
    string $id,
    string $title,
    callable $callback,
    string|array|null $screen = null,
    string $context = 'advanced',
    string $priority = 'default',
): void {}

function add_option(string $option, mixed $value = '', string $deprecated = '', bool $autoload = true): bool
{
    return true;
}

function add_query_arg(string $key, string $value, string $url): string
{
    return $url;
}

function add_settings_field(
    string $id,
    string $title,
    callable $callback,
    string $page,
    string $section = 'default',
): void {}

function add_settings_section(string $id, string $title, callable $callback, string $page): void {}

function admin_url(string $path = '', string $scheme = 'admin'): string
{
    return $path;
}

function check_ajax_referer(string $action = '-1', string|false $queryArgument = false, bool $stop = true): int|false
{
    return 1;
}

function checked(mixed $checked, mixed $current = true, bool $display = true): string
{
    return $checked === $current ? 'checked="checked"' : '';
}

function current_user_can(string $capability, mixed ...$args): bool
{
    return true;
}

/** @return array<string, string> */
function dbDelta(string $queries = '', bool $execute = true): array
{
    return [];
}

function delete_post_meta(int $postId, string $metaKey, mixed $metaValue = ''): bool
{
    return true;
}

function delete_transient(string $transient): bool
{
    return true;
}

function do_settings_sections(string $page): void {}

function esc_attr(string $text): string
{
    return $text;
}

function esc_attr__(string $text, string $domain = 'default'): string
{
    return $text;
}

function esc_html(string $text): string
{
    return $text;
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return $text;
}

function esc_textarea(string $text): string
{
    return $text;
}

function esc_url_raw(string $url, ?array $protocols = null): string
{
    return $url;
}

function flush_rewrite_rules(bool $hard = true): void {}

function get_current_screen(): ?WP_Screen
{
    return new WP_Screen();
}

function get_current_user_id(): int
{
    return 1;
}

function get_option(string $option, mixed $default = false): mixed
{
    return $default;
}

function get_post_meta(int $postId, string $key = '', bool $single = false): mixed
{
    return '';
}

function get_post_status(int|WP_Post $post = 0): string|false
{
    return 'draft';
}

function get_post_type(int|WP_Post|null $post = null): string|false
{
    return '';
}

function get_the_title(int|WP_Post $post = 0): string
{
    return '';
}

function get_transient(string $transient): mixed
{
    return false;
}

function is_admin(): bool
{
    return true;
}

function load_plugin_textdomain(string $domain, bool $deprecated = false, string $pluginRelativePath = ''): bool
{
    return true;
}

function plugin_basename(string $file): string
{
    return $file;
}

function plugin_dir_url(string $file): string
{
    return '';
}

/** @param array<string, mixed> $arguments */
function register_post_meta(string $postType, string $metaKey, array $arguments): bool
{
    return true;
}

/** @param array<string, mixed> $arguments */
function register_post_type(string $postType, array $arguments = []): object
{
    return new stdClass();
}

/** @param array<string, mixed> $arguments */
function register_setting(string $optionGroup, string $optionName, array $arguments = []): void {}

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

function rest_sanitize_boolean(mixed $value): bool
{
    return (bool) $value;
}

function sanitize_email(string $email): string
{
    return $email;
}

function sanitize_key(string $key): string
{
    return $key;
}

function sanitize_text_field(string $text): string
{
    return $text;
}

function sanitize_textarea_field(string $text): string
{
    return $text;
}

function selected(mixed $selected, mixed $current = true, bool $display = true): string
{
    return $selected === $current ? 'selected="selected"' : '';
}

function set_transient(string $transient, mixed $value, int $expiration = 0): bool
{
    return true;
}

function settings_fields(string $optionGroup): void {}

function submit_button(
    string $text = 'Save Changes',
    string $type = 'primary',
    string $name = 'submit',
    bool $wrap = true,
): void {}

function update_option(string $option, mixed $value, ?bool $autoload = null): bool
{
    return true;
}

function update_post_meta(int $postId, string $metaKey, mixed $metaValue, mixed $previousValue = ''): int|bool
{
    return true;
}

function wp_create_nonce(string|int $action = -1): string
{
    return '';
}

function wp_die(string $message = ''): never
{
    throw new RuntimeException($message);
}

function wp_enqueue_script(
    string $handle,
    string $source = '',
    array $dependencies = [],
    string|bool|null $version = false,
    bool $inFooter = false,
): void {}

function wp_enqueue_style(
    string $handle,
    string $source = '',
    array $dependencies = [],
    string|bool|null $version = false,
    string $media = 'all',
): void {}

/** @param array<string, mixed> $data */
function wp_localize_script(string $handle, string $objectName, array $data): bool
{
    return true;
}

function wp_is_post_revision(int|WP_Post $post): int|false
{
    return false;
}

function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
{
    return json_encode($value, $flags, $depth);
}

function wp_nonce_field(
    int|string $action = -1,
    string $name = '_wpnonce',
    bool $referer = true,
    bool $display = true,
): string {
    return '';
}

/** @param array<string, mixed> $data */
function wp_send_json_error(array $data = [], ?int $statusCode = null, int $flags = 0): never
{
    throw new RuntimeException('JSON error response');
}

/** @param array<string, mixed> $data */
function wp_send_json_success(array $data = [], ?int $statusCode = null, int $flags = 0): never
{
    throw new RuntimeException('JSON success response');
}

function wp_unslash(mixed $value): mixed
{
    return $value;
}

function wp_verify_nonce(string $nonce, int|string $action = -1): int|false
{
    return 1;
}
