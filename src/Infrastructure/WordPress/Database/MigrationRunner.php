<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Database;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Workflow\WorkflowSettings;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class MigrationRunner
{
    private const LOCK_KEY = 'ads_tourism_migration_lock';

    private const SCHEMA_OPTION = 'ads_tourism_schema_version';

    public function __construct(
        private RelationshipTableMigration $relationships,
        private MediaLinkTableMigration $mediaLinks,
        private ImportRunTableMigration $importRuns,
    ) {}

    public function run(): void
    {
        if ((int) get_option(self::SCHEMA_OPTION, 0) >= Plugin::SCHEMA_VERSION) {
            return;
        }

        if (get_transient(self::LOCK_KEY) !== false) {
            return;
        }

        set_transient(self::LOCK_KEY, '1', 5 * MINUTE_IN_SECONDS);

        try {
            $this->relationships->up();
            $this->mediaLinks->up();
            $this->importRuns->up();
            update_option(self::SCHEMA_OPTION, Plugin::SCHEMA_VERSION);

            if (get_option(WorkflowSettings::OPTION_REQUIRE_VERIFICATION, null) === null) {
                add_option(WorkflowSettings::OPTION_REQUIRE_VERIFICATION, true);
            }

            if (get_option(PermalinkSettings::OPTION_REDIRECTS, null) === null) {
                add_option(PermalinkSettings::OPTION_REDIRECTS, ['places' => 'places-to-go'], '', false);
            }
        } finally {
            delete_transient(self::LOCK_KEY);
        }
    }
}
