<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\SEO;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final class SchemaTypeMapper
{
    /** @return non-empty-list<string> */
    public function for(ContentType $contentType): array
    {
        return match ($contentType) {
            ContentType::PLACE => ['TouristAttraction', 'Place'],
            ContentType::ACTIVITY => ['TouristAttraction'],
            ContentType::STAY => ['LodgingBusiness'],
            ContentType::OPERATOR => ['TravelAgency', 'Organization'],
            ContentType::PACKAGE => ['TouristTrip'],
        };
    }
}
