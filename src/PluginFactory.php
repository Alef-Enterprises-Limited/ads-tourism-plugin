<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Application\Fallback\FallbackResolver;
use AlefDigitalSolutions\ADSTourism\Application\Fallback\MediaFallbackResolver;
use AlefDigitalSolutions\ADSTourism\Application\ImportExport\CsvImportService;
use AlefDigitalSolutions\ADSTourism\Application\Media\MediaLinkService;
use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use AlefDigitalSolutions\ADSTourism\Application\Presentation\TemplateCandidateResolver;
use AlefDigitalSolutions\ADSTourism\Application\Relationship\RelationshipService;
use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidator;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use AlefDigitalSolutions\ADSTourism\Domain\Permalink\PermalinkBaseValidator;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\PublicationPolicy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\ImportRunTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MediaLinkTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MigrationRunner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\RelationshipTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Fallback\FallbackHooks;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Fallback\RecordFieldFallbackResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvDownloadController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvExportService;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvImportController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\CsvRejectedRowWriter;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\ImportExportAdminPage;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\TransferFileManager;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\TransferMaintenance;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\TransferSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\WordPressTourismRecordImporter;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport\WpdbImportRunRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi\DiviCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\FeaturedMediaResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaLinkMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\WordPressMediaAttachmentResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\WpdbMediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetadataRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetaValueSanitizer;
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
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\WordPressRecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\WpdbRelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TaxonomyRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\TranslationLoader;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\PublishingGate;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\VerificationHistoryMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowColumns;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;
use wpdb;

final class PluginFactory
{
    public static function create(string $pluginFile): Plugin
    {
        global $wpdb;

        /** @var wpdb $wpdb */
        $fieldSchema = new RecordFieldSchema();
        $metaSanitizer = new MetaValueSanitizer();
        $recordTypes = new WordPressRecordTypeResolver();
        $relationshipRepository = new WpdbRelationshipRepository($wpdb);
        $relationshipService = new RelationshipService($relationshipRepository, $recordTypes);
        $mediaRepository = new WpdbMediaLinkRepository($wpdb);
        $mediaService = new MediaLinkService(
            $mediaRepository,
            $recordTypes,
            new WordPressMediaAttachmentResolver(),
        );
        $permalinkSettings = new PermalinkSettings(new PermalinkBaseValidator());
        $mediaSettings = new MediaSettings($pluginFile);
        $featuredMedia = new FeaturedMediaResolver(new MediaFallbackResolver(), $mediaSettings);
        $presentationSettings = new PresentationSettings(new CustomCssSanitizer());
        $divi = new DiviCompatibility();
        $fallbackResolver = new FallbackResolver();
        $csvSecurity = new CsvSecurity();
        $csvSchema = new CsvSchema($fieldSchema);
        $csvReader = new CsvReader($csvSecurity);
        $transferSettings = new TransferSettings();
        $transferFiles = new TransferFileManager($transferSettings);
        $importRuns = new WpdbImportRunRepository($wpdb);
        $recordImporter = new WordPressTourismRecordImporter(
            $wpdb,
            $csvSchema,
            $fieldSchema,
            $metaSanitizer,
            $mediaService,
        );
        $csvImports = new CsvImportService(
            $csvReader,
            new CsvRowValidator($csvSchema),
            $recordImporter,
            new CsvRejectedRowWriter($csvSecurity),
        );
        $csvExports = new CsvExportService($wpdb, $csvSchema, $csvSecurity, $mediaRepository);

        return new Plugin(
            new ContentTypeRegistrar($permalinkSettings),
            new TaxonomyRegistrar($permalinkSettings),
            new MetadataRegistrar($fieldSchema, $metaSanitizer),
            new AdminMenu(),
            new TranslationLoader($pluginFile),
            new MigrationRunner(
                new RelationshipTableMigration($wpdb),
                new MediaLinkTableMigration($wpdb),
                new ImportRunTableMigration($wpdb),
            ),
            new RecordDetailsMetaBox($fieldSchema, $metaSanitizer),
            new RelationshipMetaBox($relationshipService, $pluginFile),
            new RelationshipSearchController($recordTypes),
            new RelationshipCleanup($relationshipRepository),
            new VerificationHistoryService(),
            new PublishingGate(new PublicationPolicy()),
            new WorkflowColumns(),
            new WorkflowSettings(),
            new VerificationHistoryMetaBox(),
            new MediaLinkMetaBox($mediaService, $pluginFile),
            new MediaCleanup($mediaRepository),
            $mediaSettings,
            $permalinkSettings,
            new PermalinkRedirector($permalinkSettings),
            new SlugHistory(),
            new FallbackHooks(
                new RecordFieldFallbackResolver($fallbackResolver),
                $featuredMedia,
            ),
            new ImportExportAdminPage($importRuns, $transferSettings, $pluginFile),
            new CsvImportController(
                $csvSchema,
                $csvReader,
                $csvImports,
                $importRuns,
                $transferFiles,
                $transferSettings,
            ),
            new CsvDownloadController(
                $csvSchema,
                $csvSecurity,
                $csvExports,
                $importRuns,
                $transferFiles,
            ),
            $transferSettings,
            new TransferMaintenance($transferFiles, $transferSettings, $importRuns),
            new TemplateLoader($pluginFile, new TemplateCandidateResolver()),
            new FrontendRenderer(
                $pluginFile,
                $fieldSchema,
                $featuredMedia,
                $mediaRepository,
                $relationshipRepository,
            ),
            new FrontendAssets($pluginFile, $presentationSettings),
            $presentationSettings,
            new BuilderMetaRegistry($fieldSchema),
            $divi,
            new SystemStatusPage($divi),
        );
    }
}
