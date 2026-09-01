<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Fallback;

use AlefDigitalSolutions\ADSTourism\Application\Fallback\FallbackResolver;
use PHPUnit\Framework\TestCase;

final class FallbackResolverTest extends TestCase
{
    public function testItUsesTheFirstUsableValueInTheDocumentedOrder(): void
    {
        $resolver = new FallbackResolver();

        self::assertSame('record', $resolver->resolve('record', 'override', 'type', 'global'));
        self::assertSame('override', $resolver->resolve('', 'override', 'type', 'global'));
        self::assertSame('type', $resolver->resolve(null, [], 'type', 'global'));
        self::assertSame('global', $resolver->resolve('  ', null, '', 'global'));
    }

    public function testMissingValuesResolveToNullInsteadOfBrokenOutput(): void
    {
        self::assertNull((new FallbackResolver())->resolve('', null, [], '  '));
    }

    public function testFalseAndZeroRemainValidConfiguredValues(): void
    {
        $resolver = new FallbackResolver();

        self::assertSame(0, $resolver->resolve(0, null, 1, 2));
        self::assertFalse($resolver->resolve(false, true, true, true));
    }
}
