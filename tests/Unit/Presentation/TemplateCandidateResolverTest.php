<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\TemplateCandidateResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Presentation\TemplateKind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TemplateCandidateResolverTest extends TestCase
{
    /** @return iterable<string, array{TemplateKind, string}> */
    public static function kindProvider(): iterable
    {
        yield 'single record' => [TemplateKind::SINGLE, 'ads_place'];
        yield 'record archive' => [TemplateKind::ARCHIVE, 'ads_stay'];
        yield 'taxonomy archive' => [TemplateKind::TAXONOMY, 'ads_geo_area'];
    }

    #[DataProvider('kindProvider')]
    public function testSpecificThemeOverridePrecedesGenericOverride(TemplateKind $kind, string $objectName): void
    {
        $resolver = new TemplateCandidateResolver();

        self::assertSame([
            sprintf('ads-tourism/%s-%s.php', $kind->value, $objectName),
            sprintf('ads-tourism/%s.php', $kind->value),
        ], $resolver->resolve($kind, $objectName));
    }

    public function testGenericCandidateWorksWithoutAnObjectName(): void
    {
        $resolver = new TemplateCandidateResolver();

        self::assertSame(['ads-tourism/archive.php'], $resolver->resolve(TemplateKind::ARCHIVE));
    }
}
