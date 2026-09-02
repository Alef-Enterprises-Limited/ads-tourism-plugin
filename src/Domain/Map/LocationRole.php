<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Map;

enum LocationRole: string
{
    case PRIMARY = 'primary';
    case ENTRANCE = 'entrance';
    case MEETING_POINT = 'meeting_point';
    case VIEWPOINT = 'viewpoint';
    case WAYPOINT = 'waypoint';
    case OTHER = 'other';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::PRIMARY->value => 'Primary location',
            self::ENTRANCE->value => 'Entrance',
            self::MEETING_POINT->value => 'Meeting point',
            self::VIEWPOINT->value => 'Viewpoint',
            self::WAYPOINT->value => 'Waypoint',
            self::OTHER->value => 'Other',
        ];
    }
}
