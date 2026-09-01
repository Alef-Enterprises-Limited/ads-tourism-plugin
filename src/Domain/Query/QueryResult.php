<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Query;

final readonly class QueryResult
{
    /** @param list<positive-int> $postIds */
    public function __construct(
        public array $postIds,
        public int $total,
        public int $totalPages,
        public int $page,
        public int $perPage,
    ) {}
}
