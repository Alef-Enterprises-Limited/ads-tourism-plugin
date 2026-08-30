<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Taxonomy;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

enum TourismTaxonomy: string
{
    case PLACE_TYPE = 'ads_place_type';
    case ACTIVITY_TYPE = 'ads_activity_type';
    case STAY_TYPE = 'ads_stay_type';
    case PACKAGE_TYPE = 'ads_package_type';
    case AMENITY = 'ads_amenity';
    case TRAVELLER = 'ads_traveller';
    case ACCESSIBILITY = 'ads_accessibility';
    case TOURISM_TAG = 'ads_tourism_tag';
    case GEOGRAPHIC_AREA = 'ads_geographic_area';

    public function isHierarchical(): bool
    {
        return $this !== self::TOURISM_TAG;
    }

    public function rewriteBase(): string
    {
        return match ($this) {
            self::PLACE_TYPE => 'place-type',
            self::ACTIVITY_TYPE => 'activity-type',
            self::STAY_TYPE => 'stay-type',
            self::PACKAGE_TYPE => 'package-type',
            self::AMENITY => 'amenity',
            self::TRAVELLER => 'traveller',
            self::ACCESSIBILITY => 'accessibility',
            self::TOURISM_TAG => 'tourism-tag',
            self::GEOGRAPHIC_AREA => 'area',
        };
    }

    /**
     * @return list<string>
     */
    public function objectTypes(): array
    {
        return match ($this) {
            self::PLACE_TYPE => [ContentType::PLACE->value],
            self::ACTIVITY_TYPE => [ContentType::ACTIVITY->value, ContentType::PACKAGE->value],
            self::STAY_TYPE => [ContentType::STAY->value, ContentType::PACKAGE->value],
            self::PACKAGE_TYPE => [ContentType::PACKAGE->value],
            self::AMENITY => [ContentType::STAY->value, ContentType::PACKAGE->value],
            self::TRAVELLER,
            self::ACCESSIBILITY,
            self::TOURISM_TAG,
            self::GEOGRAPHIC_AREA => array_map(
                static fn(ContentType $contentType): string => $contentType->value,
                ContentType::cases(),
            ),
        };
    }
}
