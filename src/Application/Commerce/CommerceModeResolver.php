<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Commerce;

use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceResolution;

final class CommerceModeResolver
{
    public function resolve(
        CommerceMode $requestedMode,
        bool $woocommerceAvailable,
        ?int $validProductId,
        CommerceMode $fallbackMode = CommerceMode::CATALOGUE,
    ): CommerceResolution {
        if ($requestedMode !== CommerceMode::WOOCOMMERCE) {
            return new CommerceResolution($requestedMode, $requestedMode);
        }

        if ($woocommerceAvailable && $validProductId !== null && $validProductId > 0) {
            return new CommerceResolution($requestedMode, CommerceMode::WOOCOMMERCE, $validProductId);
        }

        $fallbackMode = $fallbackMode === CommerceMode::ENQUIRY
            ? CommerceMode::ENQUIRY
            : CommerceMode::CATALOGUE;

        return new CommerceResolution($requestedMode, $fallbackMode);
    }
}
