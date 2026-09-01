<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Media;

enum MediaRole: string
{
    case FEATURED = 'featured';
    case HERO = 'hero';
    case GALLERY = 'gallery';
    case THUMBNAIL = 'thumbnail';
    case ROOM = 'room';
    case ACTIVITY = 'activity';
    case LANDSCAPE = 'landscape';
    case FACILITY = 'facility';
    case MAP = 'map';
    case ITINERARY = 'itinerary';
    case OPERATOR_LOGO = 'operator_logo';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::FEATURED->value => 'Featured',
            self::HERO->value => 'Hero',
            self::GALLERY->value => 'Gallery',
            self::THUMBNAIL->value => 'Thumbnail',
            self::ROOM->value => 'Room',
            self::ACTIVITY->value => 'Activity',
            self::LANDSCAPE->value => 'Landscape',
            self::FACILITY->value => 'Facility',
            self::MAP->value => 'Map',
            self::ITINERARY->value => 'Itinerary',
            self::OPERATOR_LOGO->value => 'Operator logo',
        ];
    }
}
