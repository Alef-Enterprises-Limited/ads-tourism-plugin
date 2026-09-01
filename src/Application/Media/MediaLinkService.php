<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaAttachmentResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;
use InvalidArgumentException;

final readonly class MediaLinkService
{
    public function __construct(
        private MediaLinkRepository $repository,
        private RecordTypeResolver $recordTypes,
        private MediaAttachmentResolver $attachments,
    ) {}

    /** @param list<MediaLink> $mediaLinks */
    public function replace(int $entityPostId, array $mediaLinks): void
    {
        if ($this->recordTypes->resolve($entityPostId) === null) {
            throw new InvalidArgumentException('The selected post is not an ADS Tourism record.');
        }

        $seenSources = [];
        $validated = [];
        $primaryFound = false;

        foreach ($mediaLinks as $sortOrder => $mediaLink) {
            if ($mediaLink->entityPostId !== $entityPostId) {
                throw new InvalidArgumentException('A media link belongs to a different tourism record.');
            }

            if ($mediaLink->attachmentId !== null && !$this->attachments->exists($mediaLink->attachmentId)) {
                throw new InvalidArgumentException('A selected Media Library attachment does not exist.');
            }

            if (isset($seenSources[$mediaLink->sourceKey()])) {
                continue;
            }

            $seenSources[$mediaLink->sourceKey()] = true;
            $isPrimary = $mediaLink->isPrimary && !$primaryFound;
            $primaryFound = $primaryFound || $isPrimary;
            $validated[] = new MediaLink(
                $entityPostId,
                $mediaLink->attachmentId,
                $mediaLink->mediaUrl,
                $mediaLink->urlType,
                $mediaLink->role,
                $mediaLink->customTitle,
                $mediaLink->customAltText,
                $mediaLink->customCaption,
                $mediaLink->credit,
                $mediaLink->rightsNotice,
                $isPrimary,
                $sortOrder,
                $mediaLink->id,
            );
        }

        $this->repository->replaceForEntity($entityPostId, $validated);
    }

    /** @return list<MediaLink> */
    public function find(int $entityPostId, ?MediaRole $role = null): array
    {
        if ($this->recordTypes->resolve($entityPostId) === null) {
            return [];
        }

        return $this->repository->findForEntity($entityPostId, $role);
    }
}
