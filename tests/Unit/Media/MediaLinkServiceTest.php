<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Media;

use AlefDigitalSolutions\ADSTourism\Application\Media\MediaLinkService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryMediaAttachmentResolver;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryMediaLinkRepository;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryRecordTypeResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MediaLinkServiceTest extends TestCase
{
    private InMemoryMediaLinkRepository $repository;

    private MediaLinkService $service;

    protected function setUp(): void
    {
        $this->repository = new InMemoryMediaLinkRepository();
        $this->service = new MediaLinkService(
            $this->repository,
            new InMemoryRecordTypeResolver([10 => ContentType::PLACE, 20 => ContentType::STAY]),
            new InMemoryMediaAttachmentResolver([100, 101]),
        );
    }

    public function testLinksAreOrderedDeduplicatedAndHaveAtMostOnePrimary(): void
    {
        $this->service->replace(10, [
            new MediaLink(10, 100, null, null, MediaRole::HERO, isPrimary: true),
            new MediaLink(10, 101, null, null, MediaRole::GALLERY, isPrimary: true),
            new MediaLink(10, 100, null, null),
        ]);

        $links = $this->service->find(10);
        self::assertCount(2, $links);
        self::assertSame([0, 1], array_map(static fn(MediaLink $link): int => $link->sortOrder, $links));
        self::assertSame(1, count(array_filter($links, static fn(MediaLink $link): bool => $link->isPrimary)));
    }

    public function testInvalidAttachmentDoesNotReplaceExistingLinks(): void
    {
        $this->service->replace(10, [new MediaLink(10, 100, null, null)]);

        try {
            $this->service->replace(10, [new MediaLink(10, 999, null, null)]);
            self::fail('An unknown attachment should fail.');
        } catch (InvalidArgumentException) {
            self::assertSame(100, $this->service->find(10)[0]->attachmentId);
        }
    }

    public function testDetachingFromOneRecordDoesNotDeleteAnotherAssociation(): void
    {
        $this->service->replace(10, [new MediaLink(10, 100, null, null)]);
        $this->service->replace(20, [new MediaLink(20, 100, null, null)]);
        $this->service->replace(10, []);

        self::assertSame([], $this->service->find(10));
        self::assertSame(100, $this->service->find(20)[0]->attachmentId);
    }

    public function testExternalLinksDoNotRequireAnAttachment(): void
    {
        $this->service->replace(10, [
            new MediaLink(10, null, '/images/place.jpg', MediaUrlType::RELATIVE),
        ]);

        self::assertSame('/images/place.jpg', $this->service->find(10)[0]->mediaUrl);
    }
}
