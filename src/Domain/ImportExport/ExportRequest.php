<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class ExportRequest
{
    /** @param list<int> $selectedPostIds */
    public function __construct(
        public ?ContentType $contentType = null,
        public string $postStatus = '',
        public string $verificationStatus = '',
        public string $modifiedAfter = '',
        public string $modifiedBefore = '',
        public array $selectedPostIds = [],
    ) {}
}
