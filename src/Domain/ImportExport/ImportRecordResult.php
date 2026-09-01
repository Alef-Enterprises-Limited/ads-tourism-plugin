<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class ImportRecordResult
{
    public const CREATED = 'created';

    public const REJECTED = 'rejected';

    public const SKIPPED = 'skipped';

    public const UPDATED = 'updated';

    /** @param list<string> $errors */
    public function __construct(
        public string $outcome,
        public ?int $postId = null,
        public array $errors = [],
    ) {}
}
