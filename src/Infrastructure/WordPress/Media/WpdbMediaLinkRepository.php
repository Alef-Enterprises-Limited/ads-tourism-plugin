<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use RuntimeException;
use stdClass;
use Throwable;
use wpdb;

final readonly class WpdbMediaLinkRepository implements MediaLinkRepository
{
    public function __construct(private wpdb $database) {}

    public function tableName(): string
    {
        return $this->database->prefix . 'ads_tourism_media_links';
    }

    public function replaceForEntity(int $entityPostId, array $mediaLinks): void
    {
        $this->database->query('START TRANSACTION');

        try {
            $deleteQuery = $this->database->prepare(
                "DELETE FROM {$this->tableName()} WHERE entity_post_id = %d",
                $entityPostId,
            );

            if ($this->database->query($deleteQuery) === false) {
                throw new RuntimeException('Unable to replace existing tourism media links.');
            }

            foreach ($mediaLinks as $mediaLink) {
                $this->insert($mediaLink);
            }

            $this->database->query('COMMIT');
        } catch (Throwable $exception) {
            $this->database->query('ROLLBACK');
            throw $exception;
        }
    }

    public function findForEntity(int $entityPostId, ?MediaRole $role = null): array
    {
        $roleClause = $role === null ? '' : ' AND media_role = %s';
        $arguments = $role === null ? [$entityPostId] : [$entityPostId, $role->value];
        $query = $this->database->prepare(
            "SELECT * FROM {$this->tableName()}
            WHERE entity_post_id = %d{$roleClause}
            ORDER BY is_primary DESC, sort_order ASC, id ASC",
            ...$arguments,
        );
        $rows = $this->database->get_results($query);

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn(mixed $row): ?MediaLink => $row instanceof stdClass ? $this->hydrate($row) : null,
            $rows,
        )));
    }

    public function deleteForEntity(int $entityPostId): int
    {
        return $this->deleteWhere('entity_post_id', $entityPostId);
    }

    public function deleteForAttachment(int $attachmentId): int
    {
        return $this->deleteWhere('attachment_id', $attachmentId);
    }

    public function deleteOrphans(): int
    {
        $postsTable = $this->database->posts;
        $query = "DELETE media FROM {$this->tableName()} AS media
            LEFT JOIN {$postsTable} AS entity ON entity.ID = media.entity_post_id
            LEFT JOIN {$postsTable} AS attachment ON attachment.ID = media.attachment_id
            WHERE entity.ID IS NULL OR (media.attachment_id IS NOT NULL AND attachment.ID IS NULL)";
        $deleted = $this->database->query($query);

        return $deleted === false ? 0 : $deleted;
    }

    private function insert(MediaLink $mediaLink): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $inserted = $this->database->insert($this->tableName(), [
            'entity_post_id' => $mediaLink->entityPostId,
            'attachment_id' => $mediaLink->attachmentId,
            'media_url' => $mediaLink->mediaUrl,
            'url_type' => $mediaLink->urlType?->value,
            'media_role' => $mediaLink->role->value,
            'custom_title' => $mediaLink->customTitle,
            'custom_alt_text' => $mediaLink->customAltText,
            'custom_caption' => $mediaLink->customCaption,
            'credit' => $mediaLink->credit,
            'rights_notice' => $mediaLink->rightsNotice,
            'is_primary' => $mediaLink->isPrimary ? 1 : 0,
            'sort_order' => $mediaLink->sortOrder,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if ($inserted === false) {
            throw new RuntimeException('Unable to save a tourism media link.');
        }
    }

    private function deleteWhere(string $column, int $postId): int
    {
        $query = $this->database->prepare(
            "DELETE FROM {$this->tableName()} WHERE {$column} = %d",
            $postId,
        );
        $deleted = $this->database->query($query);

        return $deleted === false ? 0 : $deleted;
    }

    private function hydrate(stdClass $row): ?MediaLink
    {
        $role = MediaRole::tryFrom((string) ($row->media_role ?? ''));
        $urlTypeValue = (string) ($row->url_type ?? '');
        $urlType = $urlTypeValue === '' ? null : MediaUrlType::tryFrom($urlTypeValue);

        if ($role === null || ($urlTypeValue !== '' && $urlType === null)) {
            return null;
        }

        try {
            return new MediaLink(
                (int) ($row->entity_post_id ?? 0),
                isset($row->attachment_id) ? (int) $row->attachment_id : null,
                isset($row->media_url) ? (string) $row->media_url : null,
                $urlType,
                $role,
                (string) ($row->custom_title ?? ''),
                (string) ($row->custom_alt_text ?? ''),
                (string) ($row->custom_caption ?? ''),
                (string) ($row->credit ?? ''),
                (string) ($row->rights_notice ?? ''),
                (bool) ($row->is_primary ?? false),
                (int) ($row->sort_order ?? 0),
                (int) ($row->id ?? 0),
            );
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}
