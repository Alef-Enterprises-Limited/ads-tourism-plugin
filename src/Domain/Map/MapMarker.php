<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Map;

final readonly class MapMarker
{
    public function __construct(
        public int $postId,
        public Coordinates $coordinates,
        public string $title,
        public string $url,
        public string $contentType,
        public string $summary = '',
    ) {}

    /** @return array<string, int|float|string> */
    public function toArray(): array
    {
        return [
            'id' => $this->postId,
            'latitude' => $this->coordinates->latitude,
            'longitude' => $this->coordinates->longitude,
            'title' => $this->title,
            'url' => $this->url,
            'content_type' => $this->contentType,
            'summary' => $this->summary,
        ];
    }
}
