<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MigrationRunner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetadataRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\RecordDetailsMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipSearchController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TaxonomyRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TranslationLoader;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\PublishingGate;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\VerificationHistoryMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowColumns;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;

final readonly class Plugin
{
    public const SCHEMA_VERSION = 2;

    public const VERSION = '0.1.0';

    public function __construct(
        private ContentTypeRegistrar $contentTypes,
        private TaxonomyRegistrar $taxonomies,
        private MetadataRegistrar $metadata,
        private AdminMenu $adminMenu,
        private TranslationLoader $translations,
        private MigrationRunner $migrations,
        private RecordDetailsMetaBox $recordDetails,
        private RelationshipMetaBox $relationshipEditor,
        private RelationshipSearchController $relationshipSearch,
        private RelationshipCleanup $relationshipCleanup,
        private VerificationHistoryService $verificationHistory,
        private PublishingGate $publishingGate,
        private WorkflowColumns $workflowColumns,
        private WorkflowSettings $workflowSettings,
        private VerificationHistoryMetaBox $verificationHistoryMetaBox,
    ) {}

    public function register(): void
    {
        add_action('plugins_loaded', [$this->migrations, 'run'], 5);
        add_action('plugins_loaded', [$this->translations, 'load'], 10);
        add_action('init', [$this, 'registerContentModel']);
        add_action('admin_menu', [$this->adminMenu, 'register']);
        add_action('admin_menu', [$this->workflowSettings, 'registerMenu']);
        add_action('admin_init', [$this->workflowSettings, 'registerSettings']);
        add_action('add_meta_boxes', [$this->recordDetails, 'register']);
        add_action('add_meta_boxes', [$this->relationshipEditor, 'register']);
        add_action('add_meta_boxes', [$this->verificationHistoryMetaBox, 'register']);
        add_action('admin_enqueue_scripts', [$this->relationshipEditor, 'enqueueAssets']);
        add_action('save_post', [$this->recordDetails, 'save'], 10);
        add_action('save_post', [$this->relationshipEditor, 'save'], 20);
        add_action('save_post', [$this->verificationHistory, 'recordCurrentState'], 30);
        add_action('wp_ajax_' . RelationshipSearchController::ACTION, [$this->relationshipSearch, 'search']);
        add_action('before_delete_post', [$this->relationshipCleanup, 'deleteForPost']);
        add_filter('wp_insert_post_data', [$this->publishingGate, 'filterPostData'], 10, 2);
        add_filter('redirect_post_location', [$this->publishingGate, 'filterRedirect']);
        add_action('admin_notices', [$this->publishingGate, 'renderNotice']);
        add_action('restrict_manage_posts', [$this->workflowColumns, 'renderFilter']);
        add_action('pre_get_posts', [$this->workflowColumns, 'applyFilter']);
        $this->workflowColumns->register();
    }

    public function registerContentModel(): void
    {
        $this->contentTypes->register();
        $this->taxonomies->register();
        $this->metadata->register();
    }

    public function activate(): void
    {
        $this->registerContentModel();
        $this->migrations->run();

        update_option('ads_tourism_version', self::VERSION);
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
