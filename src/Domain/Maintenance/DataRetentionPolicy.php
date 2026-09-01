<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Maintenance;

final readonly class DataRetentionPolicy
{
    public const CONFIRMATION = 'DELETE ADS TOURISM DATA';

    public function deletionIsConfirmed(bool $requested, string $confirmation): bool
    {
        return $requested && trim($confirmation) === self::CONFIRMATION;
    }
}
