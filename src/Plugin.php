<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MigrationRunner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Fallback\FallbackHooks;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvDownloadController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvImportController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\ImportExportAdminPage;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\TransferMaintenance;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\TransferSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi\DiviCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaLinkMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetadataRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\RecordDetailsMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkRedirector;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\SlugHistory;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\BuilderMetaRegistry;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendAssets;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\PresentationSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\SystemStatusPage;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\TemplateLoader;
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
    public const SCHEMA_VERSION = 4;

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
        private MediaLinkMetaBox $mediaEditor,
        private MediaCleanup $mediaCleanup,
        private MediaSettings $mediaSettings,
        private PermalinkSettings $permalinkSettings,
        private PermalinkRedirector $permalinkRedirector,
        private SlugHistory $slugHistory,
        private FallbackHooks $fallbacks,
        private ImportExportAdminPage $importExportPage,
        private CsvImportController $csvImport,
        private CsvDownloadController $csvDownloads,
        private TransferSettings $transferSettings,
        private TransferMaintenance $transferMaintenance,
        private TemplateLoader $templates,
        private FrontendRenderer $frontendRenderer,
        private FrontendAssets $frontendAssets,
        private PresentationSettings $presentationSettings,
        private BuilderMetaRegistry $builderMeta,
        private DiviCompatibility $divi,
        private SystemStatusPage $systemStatus,
    ) {}

    public function register(): void
    {
        add_action('plugins_loaded', [$this->migrations, 'run'], 5);
        add_action('plugins_loaded', [$this->translations, 'load'], 10);
        add_action('init', [$this, 'registerContentModel']);
        add_action('admin_menu', [$this->adminMenu, 'register']);
        add_action('admin_menu', [$this->workflowSettings, 'registerMenu']);
        add_action('admin_menu', [$this->importExportPage, 'registerMenu']);
        add_action('admin_menu', [$this->systemStatus, 'registerMenu']);
        add_action('admin_init', [$this->workflowSettings, 'registerSettings']);
        add_action('admin_init', [$this->mediaSettings, 'registerSettings']);
        add_action('admin_init', [$this->permalinkSettings, 'registerSettings']);
        add_action('admin_init', [$this->transferSettings, 'registerSettings']);
        add_action('admin_init', [$this->presentationSettings, 'registerSettings']);
        add_action('add_meta_boxes', [$this->recordDetails, 'register']);
        add_action('add_meta_boxes', [$this->relationshipEditor, 'register']);
        add_action('add_meta_boxes', [$this->verificationHistoryMetaBox, 'register']);
        add_action('add_meta_boxes', [$this->mediaEditor, 'register']);
        add_action('admin_enqueue_scripts', [$this->relationshipEditor, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this->mediaEditor, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this->mediaSettings, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this->importExportPage, 'enqueueAssets']);
        add_action('save_post', [$this->recordDetails, 'save'], 10);
        add_action('save_post', [$this->relationshipEditor, 'save'], 20);
        add_action('save_post', [$this->mediaEditor, 'save'], 25);
        add_action('save_post', [$this->verificationHistory, 'recordCurrentState'], 30);
        add_action('wp_ajax_' . RelationshipSearchController::ACTION, [$this->relationshipSearch, 'search']);
        add_action('wp_ajax_' . CsvImportController::ACTION_UPLOAD, [$this->csvImport, 'upload']);
        add_action('wp_ajax_' . CsvImportController::ACTION_PREVIEW, [$this->csvImport, 'preview']);
        add_action('wp_ajax_' . CsvImportController::ACTION_BATCH, [$this->csvImport, 'batch']);
        add_action('admin_post_' . CsvDownloadController::ACTION_TEMPLATE, [$this->csvDownloads, 'template']);
        add_action('admin_post_' . CsvDownloadController::ACTION_EXPORT, [$this->csvDownloads, 'export']);
        add_action('admin_post_' . CsvDownloadController::ACTION_REJECTED, [$this->csvDownloads, 'rejected']);
        add_action(TransferMaintenance::HOOK, [$this->transferMaintenance, 'cleanup']);
        add_action('before_delete_post', [$this->relationshipCleanup, 'deleteForPost']);
        add_action('before_delete_post', [$this->mediaCleanup, 'deleteForPost']);
        add_action('post_updated', [$this->slugHistory, 'record'], 10, 3);
        add_action(
            'update_option_' . PermalinkSettings::OPTION_BASES,
            [$this->permalinkSettings, 'handleUpdated'],
            10,
            2,
        );
        add_action(
            'add_option_' . PermalinkSettings::OPTION_BASES,
            [$this->permalinkSettings, 'handleAdded'],
            10,
            2,
        );
        add_action('template_redirect', [$this->permalinkRedirector, 'redirectOldUrl']);
        add_action('wp_enqueue_scripts', [$this->frontendAssets, 'enqueue']);
        add_filter('wp_insert_post_data', [$this->publishingGate, 'filterPostData'], 10, 2);
        add_filter('redirect_post_location', [$this->publishingGate, 'filterRedirect']);
        add_action('admin_notices', [$this->publishingGate, 'renderNotice']);
        add_action('restrict_manage_posts', [$this->workflowColumns, 'renderFilter']);
        add_action('pre_get_posts', [$this->workflowColumns, 'applyFilter']);
        $this->workflowColumns->register();
        $this->fallbacks->register();
        $this->templates->register();
        $this->frontendRenderer->register();
        $this->builderMeta->register();
        $this->divi->register();
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
        $this->transferMaintenance->schedule();

        update_option('ads_tourism_version', self::VERSION);
        flush_rewrite_rules();
    }

    public function deactivate(): void
    {
        $this->transferMaintenance->unschedule();
        flush_rewrite_rules();
    }
}
