<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Media;

use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaLink;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaUrlType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MediaLinkTest extends TestCase
{
    public function testAnAttachmentLinkUsesOnlyAnAttachmentId(): void
    {
        $link = new MediaLink(10, 100, null, null);

        self::assertSame('attachment:100', $link->sourceKey());
    }

    public function testRelativeAndHttpsLinksAreAccepted(): void
    {
        $relative = new MediaLink(10, null, '/wp-content/uploads/image.jpg', MediaUrlType::RELATIVE);
        $absolute = new MediaLink(10, null, 'https://cdn.example.com/image.jpg', MediaUrlType::ABSOLUTE);

        self::assertSame('url:/wp-content/uploads/image.jpg', $relative->sourceKey());
        self::assertSame('url:https://cdn.example.com/image.jpg', $absolute->sourceKey());
    }

    /** @return iterable<string, array{?int, ?string, ?MediaUrlType}> */
    public static function invalidSources(): iterable
    {
        yield 'no source' => [null, null, null];
        yield 'two sources' => [100, 'https://example.com/image.jpg', MediaUrlType::ABSOLUTE];
        yield 'insecure URL' => [null, 'http://example.com/image.jpg', MediaUrlType::ABSOLUTE];
        yield 'protocol-relative URL' => [null, '//example.com/image.jpg', MediaUrlType::RELATIVE];
        yield 'attachment with URL type' => [100, null, MediaUrlType::ABSOLUTE];
    }

    #[DataProvider('invalidSources')]
    public function testInvalidSourceCombinationsAreRejected(
        ?int $attachmentId,
        ?string $url,
        ?MediaUrlType $urlType,
    ): void {
        $this->expectException(InvalidArgumentException::class);

        new MediaLink(10, $attachmentId, $url, $urlType);
    }
}
