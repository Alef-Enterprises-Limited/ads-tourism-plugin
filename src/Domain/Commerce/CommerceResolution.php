<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Commerce;

final readonly class CommerceResolution
{
    public function __construct(
        public CommerceMode $requestedMode,
        public CommerceMode $effectiveMode,
        public ?int $productId = null,
    ) {}

    public function usesWooCommerce(): bool
    {
        return $this->effectiveMode === CommerceMode::WOOCOMMERCE && $this->productId !== null;
    }

    public function fellBack(): bool
    {
        return $this->requestedMode !== $this->effectiveMode;
    }
}
