<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;

final class InMemoryMediaLinkRepository implements MediaLinkRepository
{
    /** @var list<MediaLink> */
    private array $mediaLinks = [];

    public function replaceForEntity(int $entityPostId, array $mediaLinks): void
    {
        $this->mediaLinks = array_values(array_filter(
            $this->mediaLinks,
            static fn(MediaLink $link): bool => $link->entityPostId !== $entityPostId,
        ));
        $this->mediaLinks = [...$this->mediaLinks, ...$mediaLinks];
    }

    public function findForEntity(int $entityPostId, ?MediaRole $role = null): array
    {
        return array_values(array_filter(
            $this->mediaLinks,
            static fn(MediaLink $link): bool => $link->entityPostId === $entityPostId
                && ($role === null || $link->role === $role),
        ));
    }

    public function deleteForEntity(int $entityPostId): int
    {
        return $this->deleteMatching(
            static fn(MediaLink $link): bool => $link->entityPostId === $entityPostId,
        );
    }

    public function deleteForAttachment(int $attachmentId): int
    {
        return $this->deleteMatching(
            static fn(MediaLink $link): bool => $link->attachmentId === $attachmentId,
        );
    }

    public function deleteOrphans(): int
    {
        return 0;
    }

    /** @param callable(MediaLink): bool $matches */
    private function deleteMatching(callable $matches): int
    {
        $before = count($this->mediaLinks);
        $this->mediaLinks = array_values(array_filter(
            $this->mediaLinks,
            static fn(MediaLink $link): bool => !$matches($link),
        ));

        return $before - count($this->mediaLinks);
    }
}
