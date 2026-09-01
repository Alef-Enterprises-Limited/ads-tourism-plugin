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
define('WC_VERSION', '10.0.0');

class WooCommerce {}

class WC_Product
{
    public function get_meta(string $key, bool $single = true, string $context = 'view'): mixed
    {
        return '';
    }

    public function update_meta_data(string $key, mixed $value, int $metaId = 0): void {}

    public function delete_meta_data(string $key): void {}

    public function set_name(string $name): void {}

    public function set_status(string $status = 'publish'): void {}

    public function set_catalog_visibility(string $visibility = 'visible'): void {}

    public function set_short_description(string $description): void {}

    public function set_image_id(int $imageId = 0): void {}

    public function save(): int
    {
        return 1;
    }

    public function is_purchasable(): bool
    {
        return true;
    }

    public function add_to_cart_url(): string
    {
        return 'https://example.com/cart/';
    }
}

class WC_Product_Simple extends WC_Product {}

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
    /** @param array<string, mixed>|string|int $data */
    public function __construct(string $code = '', string $message = '', array|string|int $data = '') {}

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

class WP_Theme
{
    public function get(string $header): string
    {
        return '';
    }
}

class WP_Label_Collection
{
    public string $name = '';

    public string $singular_name = '';
}

class WP_Post_Type
{
    public WP_Label_Collection $labels;

    public function __construct()
    {
        $this->labels = new WP_Label_Collection();
    }
}

class WP_Taxonomy
{
    public WP_Label_Collection $labels;

    public function __construct()
    {
        $this->labels = new WP_Label_Collection();
    }
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

    public int $found_posts = 0;

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

class WP_REST_Request
{
    /** @return array<string, mixed> */
    public function get_params(): array
    {
        return [];
    }

    public function get_param(string $key): mixed
    {
        return null;
    }
}

class WP_REST_Response
{
    public function __construct(mixed $data = null, int $status = 200, array $headers = []) {}
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

function __return_true(): bool
{
    return true;
}

function _n(string $single, string $plural, int $number, string $domain = 'default'): string
{
    return $number === 1 ? $single : $plural;
}

function absint(mixed $value): int
{
    return abs((int) $value);
}

function add_action(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}

function add_shortcode(string $tag, callable $callback): void {}

function add_filter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void {}

function apply_filters(string $hookName, mixed $value, mixed ...$args): mixed
{
    return $value;
}

function has_filter(string $hookName, callable|string|array|false $callback = false): int|false
{
    return false;
}

function do_action(string $hookName, mixed ...$args): void {}

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

function determine_locale(): string
{
    return 'en_US';
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

function get_edit_post_link(int|WP_Post $post = 0, string $context = 'display'): string|null
{
    return 'post.php';
}

function get_option(string $option, mixed $default = false): mixed
{
    return $default;
}

function get_post(int|WP_Post|null $post = null, string $output = 'OBJECT', string $filter = 'raw'): WP_Post|array|null
{
    return null;
}

function get_post_field(string $field, int|WP_Post $post = 0, string $context = 'display'): string
{
    return '';
}

function get_post_type_object(string $postType): ?WP_Post_Type
{
    return new WP_Post_Type();
}

function get_post_type_archive_link(string $postType): string|false
{
    return 'https://example.com/' . $postType . '/';
}

function get_query_var(string $queryVariable, mixed $defaultValue = ''): mixed
{
    return $defaultValue;
}

function get_taxonomy(string $taxonomy): WP_Taxonomy|false
{
    return new WP_Taxonomy();
}

/** @return list<WP_Term>|false|WP_Error */
function get_the_terms(int|WP_Post $post, string $taxonomy): array|false|WP_Error
{
    return [];
}

function get_term_link(WP_Term|int|string $term, string $taxonomy = ''): string|WP_Error
{
    return '';
}

function get_the_archive_title(): string
{
    return '';
}

function get_the_archive_description(): string
{
    return '';
}

function get_the_ID(): int
{
    return 1;
}

function get_header(string $name = '', array $args = []): void {}

function get_footer(string $name = '', array $args = []): void {}

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

function is_email(string $email, bool $deprecated = false): string|false
{
    return $email;
}

/** @param string|list<string> $postTypes */
function is_singular(string|array $postTypes = ''): bool
{
    return false;
}

/** @param string|list<string> $postTypes */
function is_post_type_archive(string|array $postTypes = ''): bool
{
    return false;
}

/** @param string|list<string> $taxonomies */
function is_tax(string|array $taxonomies = '', string|int|array $terms = ''): bool
{
    return false;
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

/** @param list<string> $templateNames */
function locate_template(array $templateNames, bool $load = false, bool $loadOnce = true, array $args = []): string
{
    return '';
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

function plugin_dir_path(string $file): string
{
    return dirname($file) . '/';
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

/** @param array<string, mixed> $arguments */
function register_rest_route(string $routeNamespace, string $route, array $arguments = [], bool $override = false): bool
{
    return true;
}

function rest_ensure_response(mixed $response): WP_REST_Response
{
    return new WP_REST_Response($response);
}

function rest_url(string $path = '', string $scheme = 'rest'): string
{
    return 'https://example.com/wp-json/' . ltrim($path, '/');
}

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

function sanitize_html_class(string $class, string $fallback = ''): string
{
    return $class;
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

/**
 * @param array<string, mixed> $pairs
 * @param array<string, mixed> $attributes
 *
 * @return array<string, mixed>
 */
function shortcode_atts(array $pairs, array $attributes, string $shortcode = ''): array
{
    return array_replace($pairs, array_intersect_key($attributes, $pairs));
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

function wp_add_inline_style(string $handle, string $data): bool
{
    return true;
}

function wp_clear_scheduled_hook(string $hook, mixed ...$args): int|false
{
    return 0;
}

function wc_get_product(int|WP_Post $product = 0): WC_Product|false|null
{
    return new WC_Product();
}

function wc_get_checkout_url(): string
{
    return 'https://example.com/checkout/';
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

function wp_register_script(
    string $handle,
    string|false $source,
    array $dependencies = [],
    string|bool|null $version = false,
    bool|array $arguments = false,
): bool {
    return true;
}

function wp_enqueue_media(array $arguments = []): void {}

function wp_enqueue_style(
    string $handle,
    string $source = '',
    array $dependencies = [],
    string|bool|null $version = false,
    string $media = 'all',
): void {}

function wp_register_style(
    string $handle,
    string|false $source,
    array $dependencies = [],
    string|bool|null $version = false,
    string $media = 'all',
): bool {
    return true;
}

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

/**
 * @param string|array{int, int} $size
 * @param array<string, string>  $attributes
 */
function wp_get_attachment_image(
    int $attachmentId,
    string|array $size = 'thumbnail',
    bool $icon = false,
    array $attributes = [],
): string {
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

function wp_kses_post(mixed $data): string
{
    return is_string($data) ? $data : '';
}

function wpautop(string $text, bool $br = true): string
{
    return $text;
}

function wp_trim_words(string $text, int $number = 55, ?string $more = null): string
{
    return $text;
}

function wp_strip_all_tags(string $text, bool $removeBreaks = false): string
{
    return strip_tags($text);
}

function wp_get_theme(string $stylesheet = '', string $themeRoot = ''): WP_Theme
{
    return new WP_Theme();
}

/** @phpstan-impure */
function have_posts(): bool
{
    return false;
}

function the_post(): void {}

/** @param array<string, mixed> $arguments */
function the_posts_pagination(array $arguments = []): void {}

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

function wp_cache_get(string $key, string $group = '', bool $force = false, ?bool &$found = null): mixed
{
    $found = false;

    return false;
}

function wp_cache_set(string $key, mixed $data, string $group = '', int $expire = 0): bool
{
    return true;
}
