<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO;

use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaGraphMerger;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use WP_Post;

final readonly class SeoIntegration
{
    public function __construct(
        private SeoSettings $settings,
        private SeoPluginCompatibility $plugins,
        private SeoDataResolver $seo,
        private TourismSchemaMapper $schema,
        private SchemaGraphMerger $schemaGraphs,
    ) {}

    public function register(): void
    {
        add_action('wp_head', [$this, 'renderNativeMetadata'], 5);
        add_filter('wp_robots', [$this, 'filterRobots']);
        add_filter('get_canonical_url', [$this, 'filterCanonical'], 10, 2);
        add_filter('ads_tourism_breadcrumb_items', [$this, 'breadcrumbItems'], 10, 2);
        add_filter('wpseo_canonical', [$this, 'filterPluginCanonical']);
        add_filter('wpseo_metadesc', [$this, 'filterPluginDescription']);
        add_filter('wpseo_opengraph_image', [$this, 'filterPluginImage']);
        add_filter('wpseo_twitter_image', [$this, 'filterPluginImage']);
        add_filter('wpseo_schema_graph', [$this, 'filterSchemaGraph'], 20, 2);
        add_filter('rank_math/json_ld', [$this, 'filterRankMathSchema'], 20, 2);
    }

    public function renderNativeMetadata(): void
    {
        if (!is_singular($this->postTypes())) {
            $this->renderArchiveCanonical();

            return;
        }

        $postId = get_the_ID();
        $data = $this->seo->forPost($postId);

        if ($data === []) {
            return;
        }

        if ($this->settings->nativeSchemaEnabled($this->plugins->isActive())) {
            $schema = $this->schema->forPost($postId);
            $enabled = apply_filters('ads_tourism_seo_schema_enabled', true, $postId, $schema);

            if ($enabled === true && $schema !== []) {
                $json = wp_json_encode(
                    $schema,
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT,
                );

                if (is_string($json)) {
                    echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
                }
            }
        }

        if (!$this->plugins->isActive() && $this->settings->nativeSocialEnabled()) {
            $this->renderSocialMetadata($data);
        }
    }

    /**
     * @param array<string, bool|string> $robots
     *
     * @return array<string, bool|string>
     */
    public function filterRobots(array $robots): array
    {
        if ($this->hasFilteredUtilityState()) {
            $robots['noindex'] = true;
        }

        if (is_singular($this->postTypes())) {
            $status = VerificationStatus::tryFrom(
                (string) get_post_meta(get_the_ID(), 'ads_tourism_verification_status', true),
            );

            if ($status !== VerificationStatus::VERIFIED) {
                $robots['noindex'] = true;
            }
        }

        return $robots;
    }

    public function filterCanonical(string $canonical, WP_Post $post): string
    {
        if (ContentType::tryFrom($post->post_type) === null) {
            return $canonical;
        }

        $permalink = get_permalink($post);
        $resolved = is_string($permalink) ? $permalink : $canonical;

        return (string) apply_filters('ads_tourism_canonical_url', $resolved, $post->ID);
    }

    public function filterPluginCanonical(string $canonical): string
    {
        $data = $this->currentSeoData();

        return $data === [] ? $canonical : (string) ($data['canonical'] ?? $canonical);
    }

    public function filterPluginDescription(string $description): string
    {
        $data = $this->currentSeoData();
        $tourismDescription = (string) ($data['description'] ?? '');

        return $tourismDescription === '' ? $description : $tourismDescription;
    }

    public function filterPluginImage(string $image): string
    {
        $data = $this->currentSeoData();
        $tourismImage = (string) ($data['image'] ?? '');

        return $tourismImage === '' ? $image : $tourismImage;
    }

    /**
     * @param array<int, array<string, mixed>> $graph
     *
     * @return array<int, array<string, mixed>>
     */
    public function filterSchemaGraph(array $graph, mixed $context): array
    {
        return array_values($this->appendSchema($graph));
    }

    /**
     * @param array<int|string, array<string, mixed>> $data
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function filterRankMathSchema(array $data, mixed $jsonLd): array
    {
        return $this->appendSchema($data);
    }

    /**
     * @param list<array<string, string>> $items
     *
     * @return list<array<string, string>>
     */
    public function breadcrumbItems(array $items, int $postId = 0): array
    {
        $post = get_post($postId > 0 ? $postId : get_the_ID());

        if (!$post instanceof WP_Post) {
            return $items;
        }

        $contentType = ContentType::tryFrom($post->post_type);

        if ($contentType === null) {
            return $items;
        }

        $archive = get_post_type_archive_link($contentType->value);
        $defaults = [['label' => __('Home', 'ads-tourism'), 'url' => home_url('/')]];

        if (is_string($archive)) {
            $postType = get_post_type_object($contentType->value);
            $defaults[] = [
                'label' => $postType === null ? $contentType->value : (string) $postType->labels->name,
                'url' => $archive,
            ];
        }

        $defaults[] = ['label' => get_the_title($post), 'url' => (string) get_permalink($post)];

        return $items === [] ? $defaults : $items;
    }

    /** @param array<string, mixed> $data */
    private function renderSocialMetadata(array $data): void
    {
        $openGraph = [
            'og:type' => 'article',
            'og:title' => (string) ($data['title'] ?? ''),
            'og:description' => (string) ($data['description'] ?? ''),
            'og:url' => (string) ($data['canonical'] ?? ''),
            'og:image' => (string) ($data['image'] ?? ''),
        ];

        foreach ($openGraph as $property => $content) {
            if ($content !== '') {
                echo '<meta property="' . esc_attr($property) . '" content="' . esc_attr($content) . '">' . "\n";
            }
        }

        $twitter = [
            'twitter:card' => (string) ($data['image'] ?? '') === '' ? 'summary' : 'summary_large_image',
            'twitter:title' => (string) ($data['title'] ?? ''),
            'twitter:description' => (string) ($data['description'] ?? ''),
            'twitter:image' => (string) ($data['image'] ?? ''),
        ];

        foreach ($twitter as $name => $content) {
            if ($content !== '') {
                echo '<meta name="' . esc_attr($name) . '" content="' . esc_attr($content) . '">' . "\n";
            }
        }
    }

    private function renderArchiveCanonical(): void
    {
        if ($this->plugins->isActive() || $this->hasFilteredUtilityState()) {
            return;
        }

        foreach (ContentType::cases() as $contentType) {
            if (!is_post_type_archive($contentType->value)) {
                continue;
            }

            $url = get_post_type_archive_link($contentType->value);

            if (is_string($url)) {
                echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
            }

            return;
        }
    }

    /** @return array<string, mixed> */
    private function currentSeoData(): array
    {
        return is_singular($this->postTypes()) ? $this->seo->forPost(get_the_ID()) : [];
    }

    /**
     * @param array<int|string, array<string, mixed>> $graph
     *
     * @return array<int|string, array<string, mixed>>
     */
    private function appendSchema(array $graph): array
    {
        if (!is_singular($this->postTypes()) || $this->settings->mode() !== 'auto') {
            return $graph;
        }

        $schema = $this->schema->forPost(get_the_ID());
        if ($schema === []) {
            return $graph;
        }

        return $this->schemaGraphs->appendWithoutDuplicate($graph, $schema);
    }

    /** @return list<string> */
    private function postTypes(): array
    {
        return array_map(static fn(ContentType $contentType): string => $contentType->value, ContentType::cases());
    }

    private function hasFilteredUtilityState(): bool
    {
        foreach (array_keys($_GET) as $key) {
            if (is_string($key) && str_starts_with($key, 'ads_')) {
                return true;
            }
        }

        return false;
    }
}
