<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\BuilderMetaRegistry;
use PHPUnit\Framework\TestCase;

final class BuilderMetaRegistryTest extends TestCase
{
    public function testItExposesSimpleRegisteredMetaWithoutPrivateOrComplexFields(): void
    {
        $registry = new BuilderMetaRegistry(new RecordFieldSchema());
        $keys = $registry->filterKeys(['existing_key'], ContentType::PLACE->value);

        self::assertContains('existing_key', $keys);
        self::assertContains('ads_tourism_summary', $keys);
        self::assertContains('ads_tourism_latitude', $keys);
        self::assertNotContains('ads_tourism_verification_notes', $keys);
        self::assertNotContains('ads_tourism_display_fallback_overrides', $keys);
    }

    public function testItLeavesExistingKeysAloneForUnknownPostTypes(): void
    {
        $registry = new BuilderMetaRegistry(new RecordFieldSchema());

        self::assertSame(['page'], $registry->filterKeys(['page'], 'page'));
    }
}
