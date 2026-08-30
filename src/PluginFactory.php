<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism;

use AlefDigitalSolutions\ADSTourism\Application\Relationship\RelationshipService;
use AlefDigitalSolutions\ADSTourism\Application\Workflow\VerificationHistoryService;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\PublicationPolicy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ContentTypeRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\MigrationRunner;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database\RelationshipTableMigration;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetadataRegistrar;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\MetaValueSanitizer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata\RecordDetailsMetaBox;
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

        return new Plugin(
            new ContentTypeRegistrar(),
            new TaxonomyRegistrar(),
            new MetadataRegistrar($fieldSchema, $metaSanitizer),
            new AdminMenu(),
            new TranslationLoader($pluginFile),
            new MigrationRunner(new RelationshipTableMigration($wpdb)),
            new RecordDetailsMetaBox($fieldSchema, $metaSanitizer),
            new RelationshipMetaBox($relationshipService, $pluginFile),
            new RelationshipSearchController($recordTypes),
            new RelationshipCleanup($relationshipRepository),
            new VerificationHistoryService(),
            new PublishingGate(new PublicationPolicy()),
            new WorkflowColumns(),
            new WorkflowSettings(),
            new VerificationHistoryMetaBox(),
        );
    }
}
