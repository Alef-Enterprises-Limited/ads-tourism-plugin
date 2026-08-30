<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Fallback;

use AlefDigitalSolutions\ADSTourism\Application\Fallback\MediaFallbackResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;
use PHPUnit\Framework\TestCase;

final class MediaFallbackResolverTest extends TestCase
{
    public function testItReturnsTheFirstAvailableMediaCandidate(): void
    {
        $default = new ResolvedMedia(100, null, 'content_type_default');
        $global = new ResolvedMedia(101, null, 'global_default');

        self::assertSame($default, (new MediaFallbackResolver())->resolve([null, $default, $global]));
    }

    public function testItReturnsNullWhenNoMediaIsAvailable(): void
    {
        self::assertNull((new MediaFallbackResolver())->resolve([null, null]));
    }
}
