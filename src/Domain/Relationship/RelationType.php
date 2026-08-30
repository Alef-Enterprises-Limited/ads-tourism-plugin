<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Relationship;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

enum RelationType: string
{
    case ACTIVITY_AVAILABLE_AT_PLACE = 'activity_available_at_place';
    case STAY_LOCATED_AT_PLACE = 'stay_located_at_place';
    case STAY_NEAR_PLACE = 'stay_near_place';
    case OPERATOR_SERVES_PLACE = 'operator_serves_place';
    case ACTIVITY_PROVIDED_BY_OPERATOR = 'activity_provided_by_operator';
    case STAY_MANAGED_BY_OPERATOR = 'stay_managed_by_operator';
    case PACKAGE_COVERS_PLACE = 'package_covers_place';
    case PACKAGE_INCLUDES_ACTIVITY = 'package_includes_activity';
    case PACKAGE_INCLUDES_STAY = 'package_includes_stay';
    case PACKAGE_OFFERED_BY = 'package_offered_by';
    case PACKAGE_PARTNER_PROVIDER = 'package_partner_provider';
    case ACTIVITY_NEAR_STAY = 'activity_near_stay';

    /**
     * @return list<ContentType>
     */
    public function sourceTypes(): array
    {
        return match ($this) {
            self::ACTIVITY_AVAILABLE_AT_PLACE,
            self::ACTIVITY_PROVIDED_BY_OPERATOR,
            self::ACTIVITY_NEAR_STAY => [ContentType::ACTIVITY],
            self::STAY_LOCATED_AT_PLACE,
            self::STAY_NEAR_PLACE,
            self::STAY_MANAGED_BY_OPERATOR => [ContentType::STAY],
            self::OPERATOR_SERVES_PLACE => [ContentType::OPERATOR],
            self::PACKAGE_COVERS_PLACE,
            self::PACKAGE_INCLUDES_ACTIVITY,
            self::PACKAGE_INCLUDES_STAY,
            self::PACKAGE_OFFERED_BY,
            self::PACKAGE_PARTNER_PROVIDER => [ContentType::PACKAGE],
        };
    }

    /**
     * @return list<ContentType>
     */
    public function targetTypes(): array
    {
        return match ($this) {
            self::ACTIVITY_AVAILABLE_AT_PLACE,
            self::STAY_LOCATED_AT_PLACE,
            self::STAY_NEAR_PLACE,
            self::OPERATOR_SERVES_PLACE,
            self::PACKAGE_COVERS_PLACE => [ContentType::PLACE],
            self::ACTIVITY_PROVIDED_BY_OPERATOR,
            self::STAY_MANAGED_BY_OPERATOR => [ContentType::OPERATOR],
            self::PACKAGE_INCLUDES_ACTIVITY => [ContentType::ACTIVITY],
            self::PACKAGE_INCLUDES_STAY,
            self::ACTIVITY_NEAR_STAY => [ContentType::STAY],
            self::PACKAGE_OFFERED_BY,
            self::PACKAGE_PARTNER_PROVIDER => [ContentType::OPERATOR, ContentType::STAY],
        };
    }

    public function sideFor(ContentType $contentType): ?RelationshipSide
    {
        if (in_array($contentType, $this->sourceTypes(), true)) {
            return RelationshipSide::SOURCE;
        }

        if (in_array($contentType, $this->targetTypes(), true)) {
            return RelationshipSide::TARGET;
        }

        return null;
    }

    /**
     * @return list<ContentType>
     */
    public function counterpartTypes(ContentType $contentType): array
    {
        return match ($this->sideFor($contentType)) {
            RelationshipSide::SOURCE => $this->targetTypes(),
            RelationshipSide::TARGET => $this->sourceTypes(),
            null => [],
        };
    }

    public function allowsPrimary(): bool
    {
        return $this === self::STAY_LOCATED_AT_PLACE || $this === self::PACKAGE_OFFERED_BY;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVITY_AVAILABLE_AT_PLACE => 'Available at places',
            self::STAY_LOCATED_AT_PLACE => 'Located at place',
            self::STAY_NEAR_PLACE => 'Nearby places',
            self::OPERATOR_SERVES_PLACE => 'Served places',
            self::ACTIVITY_PROVIDED_BY_OPERATOR => 'Provided by operators',
            self::STAY_MANAGED_BY_OPERATOR => 'Managed by operators',
            self::PACKAGE_COVERS_PLACE => 'Covered places',
            self::PACKAGE_INCLUDES_ACTIVITY => 'Included activities',
            self::PACKAGE_INCLUDES_STAY => 'Included stays',
            self::PACKAGE_OFFERED_BY => 'Offered by',
            self::PACKAGE_PARTNER_PROVIDER => 'Partner providers',
            self::ACTIVITY_NEAR_STAY => 'Nearby stays',
        };
    }

    public function labelFor(ContentType $contentType): string
    {
        if ($this->sideFor($contentType) !== RelationshipSide::TARGET) {
            return $this->label();
        }

        return match ($this) {
            self::ACTIVITY_AVAILABLE_AT_PLACE => 'Available activities',
            self::STAY_LOCATED_AT_PLACE => 'Located stays',
            self::STAY_NEAR_PLACE => 'Nearby stays',
            self::OPERATOR_SERVES_PLACE => 'Serving operators',
            self::ACTIVITY_PROVIDED_BY_OPERATOR => 'Provided activities',
            self::STAY_MANAGED_BY_OPERATOR => 'Managed stays',
            self::PACKAGE_COVERS_PLACE => 'Packages covering this place',
            self::PACKAGE_INCLUDES_ACTIVITY => 'Packages including this activity',
            self::PACKAGE_INCLUDES_STAY => 'Packages including this stay',
            self::PACKAGE_OFFERED_BY => 'Packages offered here',
            self::PACKAGE_PARTNER_PROVIDER => 'Partner packages',
            self::ACTIVITY_NEAR_STAY => 'Nearby activities',
        };
    }

    /**
     * @return list<self>
     */
    public static function forContentType(ContentType $contentType): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn(self $relationType): bool => $relationType->sideFor($contentType) !== null,
        ));
    }
}
