<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceModeResolver;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Application\Fallback\FallbackResolver;
use AlefDigitalSolutions\ADSTourism\Application\Fallback\MediaFallbackResolver;
use AlefDigitalSolutions\ADSTourism\Application\ImportExport\CsvImportService;
use AlefDigitalSolutions\ADSTourism\Application\Media\MediaLinkService;
use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use AlefDigitalSolutions\ADSTourism\Application\Presentation\TemplateCandidateResolver;
use AlefDigitalSolutions\ADSTourism\Application\Query\TourismQueryFactory;
use AlefDigitalSolutions\ADSTourism\Application\Relationship\RelationshipService;
use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaGraphMerger;
use AlefDigitalSolutions\ADSTourism\Application\SEO\SchemaTypeMapper;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ShortcodeContextRegistry;
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
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\CommerceActionController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\CommerceSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\CommerceShortcodes;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\HposCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\PackageCommerceMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\PackageCommerceResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\PackageProductCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\PackageProductDataFactory;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\PackageRecordUrlFilter;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\ProductPageTourismContext;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WooCommerceCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WooCommerceIntegration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WooCommerceProductGateway;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce\WordPressPackageProductLinkStore;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance\IntegrityScanner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance\MaintenancePage;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance\MaintenanceSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Maintenance\PrivacyPolicyGuide;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\GoogleMapsProvider;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapAssets;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapMarkerFactory;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapProviderRegistry;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapShortcodes;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\FeaturedMediaResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaLinkMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\MediaSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\WordPressMediaAttachmentResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media\WpdbMediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetadataRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetaValueSanitizer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\RecordDetailsMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\MultilingualSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\WordPressTranslationAdapter;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkRedirector;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\SlugHistory;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\BuilderMetaRegistry;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendAssets;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\PresentationSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\SystemStatusPage;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\TemplateLoader;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\PublicQueryCache;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\PublicQueryController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\QueryCacheInvalidator;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query\WordPressQueryService;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipCleanup;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipMetaBox;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\RelationshipSearchController;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\WordPressRecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship\WpdbRelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\SeoDataResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\SeoIntegration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\SeoPluginCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\SeoSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\TourismSchemaMapper;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\InteractiveShortcodes;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ListingRenderer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\PaginationRenderer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\RecordComponentShortcodes;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ShortcodeAssets;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ShortcodeDiagnostic;
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
        $frontendAssets = new FrontendAssets($pluginFile, $presentationSettings);
        $multilingualSettings = new MultilingualSettings();
        $translations = new TranslationResolver(new WordPressTranslationAdapter(), $multilingualSettings);
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
        $frontendRenderer = new FrontendRenderer(
            $pluginFile,
            $fieldSchema,
            $featuredMedia,
            $mediaRepository,
            $relationshipRepository,
            $translations,
        );
        $queryFactory = new TourismQueryFactory();
        $queryCache = new PublicQueryCache();
        $queryService = new WordPressQueryService($relationshipRepository, $queryCache);
        $listingRenderer = new ListingRenderer($frontendRenderer);
        $paginationRenderer = new PaginationRenderer();
        $shortcodeAssets = new ShortcodeAssets($pluginFile, $frontendAssets);
        $shortcodeDiagnostics = new ShortcodeDiagnostic();
        $shortcodeContexts = new ShortcodeContextRegistry();
        $mapSettings = new MapSettings();
        $googleMaps = new GoogleMapsProvider($mapSettings);
        $mapProviders = new MapProviderRegistry([$googleMaps], $mapSettings);
        $mapMarkers = new MapMarkerFactory($translations);
        $mapAssets = new MapAssets($pluginFile, $frontendAssets);
        $seoSettings = new SeoSettings();
        $seoPlugins = new SeoPluginCompatibility();
        $seoData = new SeoDataResolver($relationshipRepository, $translations);
        $schemaMapper = new TourismSchemaMapper(new SchemaTypeMapper(), $seoData);
        $woocommerceCompatibility = new WooCommerceCompatibility();
        $woocommerceProducts = new WooCommerceProductGateway($woocommerceCompatibility);
        $packageProducts = new PackageProductService(
            new WordPressPackageProductLinkStore(),
            $woocommerceProducts,
        );
        $commerceSettings = new CommerceSettings($woocommerceCompatibility);
        $commerceResolver = new PackageCommerceResolver(
            new CommerceModeResolver(),
            $packageProducts,
            $woocommerceCompatibility,
            $commerceSettings,
        );
        $packageProductData = new PackageProductDataFactory();
        $commerceActions = new CommerceActionController($packageProducts, $packageProductData);
        $migrations = new MigrationRunner(
            new RelationshipTableMigration($wpdb),
            new MediaLinkTableMigration($wpdb),
            new ImportRunTableMigration($wpdb),
        );
        $integrity = new IntegrityScanner($wpdb, $relationshipRepository, $mediaRepository);

        return new Plugin(
            new ContentTypeRegistrar($permalinkSettings),
            new TaxonomyRegistrar($permalinkSettings),
            new MetadataRegistrar($fieldSchema, $metaSanitizer),
            new AdminMenu(),
            new TranslationLoader($pluginFile),
            $migrations,
            new RecordDetailsMetaBox($fieldSchema, $metaSanitizer),
            new RelationshipMetaBox($relationshipService, $pluginFile),
            new RelationshipSearchController($recordTypes),
            new RelationshipCleanup($relationshipRepository),
            new VerificationHistoryService(),
            new PublishingGate(new PublicationPolicy()),
            new WorkflowColumns(),
            new WorkflowSettings(),
            new MaintenanceSettings(),
            new MaintenancePage($integrity),
            new PrivacyPolicyGuide(),
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
            $frontendRenderer,
            $frontendAssets,
            $presentationSettings,
            new BuilderMetaRegistry($fieldSchema),
            $divi,
            new SystemStatusPage(
                $divi,
                $mapSettings,
                $mapProviders,
                $seoPlugins,
                $translations,
                $woocommerceCompatibility,
                $migrations,
            ),
            new PublicQueryController(
                $queryFactory,
                $queryService,
                $listingRenderer,
                $paginationRenderer,
                $mapMarkers,
            ),
            new QueryCacheInvalidator($queryCache),
            new InteractiveShortcodes(
                $queryFactory,
                $queryService,
                $shortcodeContexts,
                $listingRenderer,
                $paginationRenderer,
                $shortcodeAssets,
                $shortcodeDiagnostics,
            ),
            new RecordComponentShortcodes(
                $fieldSchema,
                $relationshipRepository,
                $frontendRenderer,
                $shortcodeAssets,
                $shortcodeDiagnostics,
                $translations,
            ),
            $shortcodeAssets,
            $mapSettings,
            new MapShortcodes(
                $mapProviders,
                $mapMarkers,
                $mapAssets,
                $queryFactory,
                $queryService,
                $shortcodeContexts,
                $shortcodeDiagnostics,
            ),
            $seoSettings,
            new SeoIntegration(
                $seoSettings,
                $seoPlugins,
                $seoData,
                $schemaMapper,
                new SchemaGraphMerger(),
            ),
            $multilingualSettings,
            new WooCommerceIntegration(
                new HposCompatibility($pluginFile),
                $commerceSettings,
                new PackageCommerceMetaBox($packageProducts, $woocommerceCompatibility, $commerceActions),
                $commerceActions,
                new PackageProductCleanup($packageProducts),
                new CommerceShortcodes(
                    $commerceResolver,
                    $woocommerceProducts,
                    $commerceSettings,
                    $shortcodeAssets,
                    $shortcodeDiagnostics,
                ),
                new ProductPageTourismContext($woocommerceProducts),
                new PackageRecordUrlFilter($commerceSettings, $commerceResolver, $woocommerceProducts),
            ),
        );
    }
}
