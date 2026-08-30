<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

interface MediaLinkRepository
{
    /** @param list<MediaLink> $mediaLinks */
    public function replaceForEntity(int $entityPostId, array $mediaLinks): void;

    /** @return list<MediaLink> */
    public function findForEntity(int $entityPostId, ?MediaRole $role = null): array;

    public function deleteForEntity(int $entityPostId): int;

    public function deleteForAttachment(int $attachmentId): int;

    public function deleteOrphans(): int;
}
