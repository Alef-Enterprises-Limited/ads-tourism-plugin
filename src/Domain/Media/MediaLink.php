<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

use InvalidArgumentException;

final readonly class MediaLink
{
    public function __construct(
        public int $entityPostId,
        public ?int $attachmentId,
        public ?string $mediaUrl,
        public ?MediaUrlType $urlType,
        public MediaRole $role = MediaRole::GALLERY,
        public string $customTitle = '',
        public string $customAltText = '',
        public string $customCaption = '',
        public string $credit = '',
        public string $rightsNotice = '',
        public bool $isPrimary = false,
        public int $sortOrder = 0,
        public ?int $id = null,
    ) {
        $hasAttachment = $this->attachmentId !== null && $this->attachmentId > 0;
        $hasUrl = $this->mediaUrl !== null && $this->mediaUrl !== '';

        if (
            $this->entityPostId <= 0
            || ($this->attachmentId !== null && $this->attachmentId <= 0)
            || $hasAttachment === $hasUrl
        ) {
            throw new InvalidArgumentException('A media link requires one entity and exactly one media source.');
        }

        if ($hasAttachment && $this->urlType !== null) {
            throw new InvalidArgumentException('Media Library attachments do not use a URL type.');
        }

        if ($hasUrl && !$this->urlMatchesType((string) $this->mediaUrl, $this->urlType)) {
            throw new InvalidArgumentException('The media URL does not match its declared URL type.');
        }
    }

    public function sourceKey(): string
    {
        return $this->attachmentId !== null
            ? 'attachment:' . $this->attachmentId
            : 'url:' . $this->mediaUrl;
    }

    private function urlMatchesType(string $url, ?MediaUrlType $urlType): bool
    {
        return match ($urlType) {
            MediaUrlType::RELATIVE => str_starts_with($url, '/') && !str_starts_with($url, '//'),
            MediaUrlType::ABSOLUTE => str_starts_with($url, 'https://') && filter_var($url, FILTER_VALIDATE_URL) !== false,
            null => false,
        };
    }
}
