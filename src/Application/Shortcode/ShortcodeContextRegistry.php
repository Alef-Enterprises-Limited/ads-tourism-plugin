<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Shortcode;

use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;

final class ShortcodeContextRegistry
{
    /** @var array<string, array<string, int>> */
    private array $components = [];

    /** @var array<string, QueryResult> */
    private array $results = [];

    private int $automaticContext = 0;

    public function automatic(): ContextName
    {
        ++$this->automaticContext;

        return new ContextName('listing-' . $this->automaticContext);
    }

    public function register(ContextName $context, ContextComponent $component): ContextRegistration
    {
        $components = $this->components[$context->value] ?? [];

        if ($component->isPrimary() && (($components[ContextComponent::RESULTS->value] ?? 0) > 0
            || ($components[ContextComponent::LISTING->value] ?? 0) > 0)) {
            return new ContextRegistration(false, 'Only one primary result component is allowed per context.');
        }

        $components[$component->value] = ($components[$component->value] ?? 0) + 1;
        $this->components[$context->value] = $components;

        return new ContextRegistration(true);
    }

    public function storeResult(ContextName $context, QueryResult $result): void
    {
        $this->results[$context->value] = $result;
    }

    public function result(ContextName $context): ?QueryResult
    {
        return $this->results[$context->value] ?? null;
    }
}
