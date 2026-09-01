<?php

declare(strict_types=1);

/**
 * Minimal WordPress declarations used only by PHPStan.
 *
 * Runtime code always uses the real WordPress functions and classes.
 */

define('ABSPATH', '/');
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);
define('PCLZIP_OPT_REMOVE_PATH', 77001);

class WP_Post
{
    public int $ID = 0;

    public string $post_type = '';

    public string $post_status = '';

    public string $post_name = '';

    public string $post_title = '';

    public string $post_content = '';

    public string $post_excerpt = '';

    public int $post_parent = 0;
}

class WP_Error
{
    public function get_error_message(string $code = ''): string
    {
        return '';
    }
}

class WP_Term
{
    public int $term_id = 0;

    public int $parent = 0;

    public string $slug = '';

    public string $name = '';

    public string $description = '';
}

class PclZip
{
    public function __construct(string $filename) {}

    public function create(array $files, mixed ...$options): int|array
    {
        return 1;
    }
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

    /**
     * @param array<string, mixed> $data
     * @param list<string>|null    $format
     */
    public function insert(string $table, array $data, ?array $format = null): int|false
    {
        return 1;
    }

    /** @return list<object>|null */
    public function get_results(string $query): ?array
    {
        return [];
    }

    public function get_row(string $query): ?object
    {
        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $where
     */
    public function update(string $table, array $data, array $where): int|false
    {
        return 1;
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

/** @param string|array<string, string> $key */
function add_query_arg(string|array $key, mixed $value = null, ?string $url = null): string
{
    return $url ?? (is_string($value) ? $value : '');
}

function add_settings_field(
    string $id,
    string $title,
    callable $callback,
    string $page,
    string $section = 'default',
): void {}

function add_settings_section(string $id, string $title, callable $callback, string $page): void {}

function add_settings_error(
    string $setting,
    string $code,
    string $message,
    string $type = 'error',
): void {}

function admin_url(string $path = '', string $scheme = 'admin'): string
{
    return $path;
}

function check_ajax_referer(string $action = '-1', string|false $queryArgument = false, bool $stop = true): int|false
{
    return 1;
}

function check_admin_referer(int|string $action = -1, string $queryArgument = '_wpnonce'): int|false
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

function delete_post_thumbnail(int|WP_Post $post): bool
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

function esc_url(string $url, ?array $protocols = null, string $context = 'display'): string
{
    return $url;
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

function get_permalink(int|WP_Post $post = 0, bool $leaveName = false): string|false
{
    return '';
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

function get_post_thumbnail_id(int|WP_Post|null $post = null): int|false
{
    return 0;
}

function get_term(int $termId, string $taxonomy = '', string $output = 'OBJECT', string $filter = 'raw'): WP_Term|WP_Error|null
{
    return null;
}

function get_term_by(string $field, string|int $value, string $taxonomy = '', string $output = 'OBJECT', string $filter = 'raw'): WP_Term|array|false
{
    return false;
}

/**
 * @param array<string, mixed> $arguments
 *
 * @return list<WP_Term>|WP_Error
 */
function get_terms(array $arguments = []): array|WP_Error
{
    return [];
}

/**
 * @param array<string, mixed> $arguments
 *
 * @return list<int|WP_Post>
 */
function get_posts(array $arguments = []): array
{
    return [];
}

function get_transient(string $transient): mixed
{
    return false;
}

function is_admin(): bool
{
    return true;
}

function is_404(): bool
{
    return false;
}

function home_url(string $path = '', ?string $scheme = null): string
{
    return 'https://example.com' . $path;
}

function is_wp_error(mixed $thing): bool
{
    return $thing instanceof WP_Error;
}

function load_plugin_textdomain(string $domain, bool $deprecated = false, string $pluginRelativePath = ''): bool
{
    return true;
}

function nocache_headers(): void {}

function plugin_basename(string $file): string
{
    return $file;
}

function plugin_dir_url(string $file): string
{
    return '';
}

function plugins_url(string $path = '', string $plugin = ''): string
{
    return $path;
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

function sanitize_file_name(string $filename): string
{
    return $filename;
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

function sanitize_title(string $title, string $fallbackTitle = '', string $context = 'save'): string
{
    return $title;
}

function selected(mixed $selected, mixed $current = true, bool $display = true): string
{
    return $selected === $current ? 'selected="selected"' : '';
}

function set_transient(string $transient, mixed $value, int $expiration = 0): bool
{
    return true;
}

function set_post_thumbnail(int|WP_Post $post, int $thumbnailId): int|bool
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

function wp_attachment_is_image(int|WP_Post $post = 0): bool
{
    return true;
}

function wp_clear_scheduled_hook(string $hook, mixed ...$args): int|false
{
    return 0;
}

function wp_create_nonce(string|int $action = -1): string
{
    return '';
}

function wp_die(string $message = ''): never
{
    throw new RuntimeException($message);
}

function wp_generate_password(int $length = 12, bool $specialChars = true, bool $extraSpecialChars = false): string
{
    return str_repeat('a', $length);
}

function wp_generate_uuid4(): string
{
    return '00000000-0000-4000-8000-000000000000';
}

function wp_enqueue_script(
    string $handle,
    string $source = '',
    array $dependencies = [],
    string|bool|null $version = false,
    bool $inFooter = false,
): void {}

function wp_enqueue_media(array $arguments = []): void {}

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

/** @param string|array{int, int} $size */
function wp_get_attachment_image_url(int $attachmentId, string|array $size = 'thumbnail', bool $icon = false): string|false
{
    return '';
}

function wp_http_validate_url(string $url): string|false
{
    return $url;
}

/**
 * @param array<string, mixed> $postarr
 */
function wp_insert_post(array $postarr, bool $wpError = false, bool $fireAfterHooks = true): int|WP_Error
{
    return 1;
}

/**
 * @param array<string, mixed> $arguments
 *
 * @return array{term_id: int, term_taxonomy_id: int}|WP_Error
 */
function wp_insert_term(string $term, string $taxonomy, array $arguments = []): array|WP_Error
{
    return ['term_id' => 1, 'term_taxonomy_id' => 1];
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

function wp_nonce_url(string $actionUrl, int|string $action = -1, string $name = '_wpnonce'): string
{
    return $actionUrl;
}

function wp_next_scheduled(string $hook, mixed ...$args): int|false
{
    return false;
}

function wp_schedule_event(int $timestamp, string $recurrence, string $hook, array $args = [], bool $wpError = false): bool|WP_Error
{
    return true;
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

function wp_parse_url(string $url, int $component = -1): array|string|int|false|null
{
    return parse_url($url, $component);
}

function wp_safe_redirect(string $location, int $status = 302, string $xRedirectBy = 'WordPress'): bool
{
    return true;
}

/**
 * @param array<string, mixed> $arguments
 *
 * @return list<string>|WP_Error
 */
function wp_get_object_terms(int|array $objectIds, string|array $taxonomies, array $arguments = []): array|WP_Error
{
    return [];
}

/**
 * @param list<int|string>|int|string $terms
 *
 * @return list<int>|WP_Error
 */
function wp_set_object_terms(int $objectId, array|int|string $terms, string $taxonomy, bool $append = false): array|WP_Error
{
    return [];
}

/** @return array<string, mixed> */
function wp_upload_dir(?string $time = null, bool $createDir = true, bool $refreshCache = false): array
{
    return ['basedir' => '/tmp'];
}

function wp_mkdir_p(string $target): bool
{
    return true;
}

function wp_verify_nonce(string $nonce, int|string $action = -1): int|false
{
    return 1;
}
