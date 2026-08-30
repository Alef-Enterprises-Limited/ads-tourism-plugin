<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Relationship\Relationship;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipSide;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use RuntimeException;
use stdClass;
use Throwable;
use wpdb;

final readonly class WpdbRelationshipRepository implements RelationshipRepository
{
    public function __construct(private wpdb $database) {}

    public function tableName(): string
    {
        return $this->database->prefix . 'ads_tourism_relations';
    }

    public function replaceForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
        array $relationships,
    ): void {
        $column = $side === RelationshipSide::SOURCE ? 'source_post_id' : 'target_post_id';
        $this->database->query('START TRANSACTION');

        try {
            $deleteQuery = $this->database->prepare(
                "DELETE FROM {$this->tableName()} WHERE {$column} = %d AND relation_key = %s",
                $postId,
                $relationType->value,
            );

            if ($this->database->query($deleteQuery) === false) {
                throw new RuntimeException('Unable to replace existing tourism relationships.');
            }

            foreach ($relationships as $relationship) {
                $this->insert($relationship);
            }

            $this->database->query('COMMIT');
        } catch (Throwable $exception) {
            $this->database->query('ROLLBACK');
            throw $exception;
        }
    }

    public function findForRecord(
        int $postId,
        RelationType $relationType,
        RelationshipSide $side,
    ): array {
        $column = $side === RelationshipSide::SOURCE ? 'source_post_id' : 'target_post_id';
        $query = $this->database->prepare(
            "SELECT * FROM {$this->tableName()}
            WHERE {$column} = %d AND relation_key = %s
            ORDER BY is_primary DESC, sort_order ASC, id ASC",
            $postId,
            $relationType->value,
        );
        $rows = $this->database->get_results($query);

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn(mixed $row): ?Relationship => $row instanceof stdClass ? $this->hydrate($row) : null,
            $rows,
        )));
    }

    public function deleteForPost(int $postId): int
    {
        $query = $this->database->prepare(
            "DELETE FROM {$this->tableName()} WHERE source_post_id = %d OR target_post_id = %d",
            $postId,
            $postId,
        );
        $deleted = $this->database->query($query);

        return $deleted === false ? 0 : $deleted;
    }

    public function deleteOrphans(): int
    {
        $postsTable = $this->database->posts;
        $query = "DELETE relations FROM {$this->tableName()} AS relations
            LEFT JOIN {$postsTable} AS source ON source.ID = relations.source_post_id
            LEFT JOIN {$postsTable} AS target ON target.ID = relations.target_post_id
            WHERE source.ID IS NULL OR target.ID IS NULL";
        $deleted = $this->database->query($query);

        return $deleted === false ? 0 : $deleted;
    }

    private function insert(Relationship $relationship): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $encodedMetadata = wp_json_encode($relationship->metadata);
        $metadataJson = $relationship->metadata === [] || !is_string($encodedMetadata)
            ? null
            : $encodedMetadata;
        $query = $this->database->prepare(
            "INSERT INTO {$this->tableName()}
                (source_post_id, target_post_id, relation_key, is_primary, sort_order, metadata_json, created_at, updated_at)
            VALUES (%d, %d, %s, %d, %d, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                is_primary = VALUES(is_primary),
                sort_order = VALUES(sort_order),
                metadata_json = VALUES(metadata_json),
                updated_at = VALUES(updated_at)",
            $relationship->sourcePostId,
            $relationship->targetPostId,
            $relationship->type->value,
            $relationship->isPrimary ? 1 : 0,
            $relationship->sortOrder,
            $metadataJson,
            $now,
            $now,
        );

        if ($this->database->query($query) === false) {
            throw new RuntimeException('Unable to save a tourism relationship.');
        }
    }

    private function hydrate(stdClass $row): ?Relationship
    {
        $type = RelationType::tryFrom((string) ($row->relation_key ?? ''));

        if ($type === null) {
            return null;
        }

        $metadata = json_decode((string) ($row->metadata_json ?? ''), true);

        return new Relationship(
            (int) ($row->source_post_id ?? 0),
            (int) ($row->target_post_id ?? 0),
            $type,
            (bool) ($row->is_primary ?? false),
            (int) ($row->sort_order ?? 0),
            is_array($metadata) ? $metadata : [],
            (int) ($row->id ?? 0),
        );
    }
}
