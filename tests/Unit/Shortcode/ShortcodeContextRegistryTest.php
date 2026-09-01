<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Shortcode;

use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ContextComponent;
use AlefDigitalSolutions\ADSTourism\Application\Shortcode\ShortcodeContextRegistry;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use PHPUnit\Framework\TestCase;

final class ShortcodeContextRegistryTest extends TestCase
{
    public function testIndependentContextsCanEachOwnAResultsComponent(): void
    {
        $registry = new ShortcodeContextRegistry();

        self::assertTrue($registry->register(new ContextName('places'), ContextComponent::RESULTS)->accepted);
        self::assertTrue($registry->register(new ContextName('packages'), ContextComponent::RESULTS)->accepted);
    }

    public function testAContextRejectsASecondPrimaryComponent(): void
    {
        $registry = new ShortcodeContextRegistry();
        $context = new ContextName('discover');

        self::assertTrue($registry->register($context, ContextComponent::RESULTS)->accepted);
        $duplicate = $registry->register($context, ContextComponent::LISTING);

        self::assertFalse($duplicate->accepted);
        self::assertSame('Only one primary result component is allowed per context.', $duplicate->message);
    }

    public function testAContextAcceptsMultiplePaginationComponents(): void
    {
        $registry = new ShortcodeContextRegistry();
        $context = new ContextName('discover');

        self::assertTrue($registry->register($context, ContextComponent::PAGINATION)->accepted);
        self::assertTrue($registry->register($context, ContextComponent::PAGINATION)->accepted);
    }

    public function testAContextAcceptsAMapAlongsideItsResults(): void
    {
        $registry = new ShortcodeContextRegistry();
        $context = new ContextName('discover');

        self::assertTrue($registry->register($context, ContextComponent::RESULTS)->accepted);
        self::assertTrue($registry->register($context, ContextComponent::MAP)->accepted);
    }

    public function testAutomaticListingContextsAreUnique(): void
    {
        $registry = new ShortcodeContextRegistry();

        self::assertSame('listing-1', $registry->automatic()->value);
        self::assertSame('listing-2', $registry->automatic()->value);
    }

    public function testItSharesTheInitialResultWithPaginationComponents(): void
    {
        $registry = new ShortcodeContextRegistry();
        $context = new ContextName('discover');
        $result = new QueryResult([10, 20], 2, 1, 1, 12);

        $registry->storeResult($context, $result);

        self::assertSame($result, $registry->result($context));
        self::assertNull($registry->result(new ContextName('other')));
    }
}
