<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use InvalidArgumentException;

final readonly class TourismQuery
{
    public const MAX_PER_PAGE = 24;

    /**
     * @param list<ContentType>           $contentTypes
     * @param array<string, list<string>> $taxonomyFilters
     * @param array<string, positive-int> $relationshipFilters
     */
    public function __construct(
        public ContextName $context,
        public array $contentTypes,
        public string $keyword = '',
        public int $page = 1,
        public int $perPage = 12,
        public QuerySort $sort = QuerySort::TITLE_ASC,
        public PaginationMode $pagination = PaginationMode::NUMBERED,
        public array $taxonomyFilters = [],
        public array $relationshipFilters = [],
        public ?float $minimumPrice = null,
        public ?float $maximumPrice = null,
        public ?int $minimumDuration = null,
        public ?int $maximumDuration = null,
    ) {
        if ($this->contentTypes === []) {
            throw new InvalidArgumentException('At least one tourism content type is required.');
        }

        if ($this->page < 1 || $this->perPage < 1 || $this->perPage > self::MAX_PER_PAGE) {
            throw new InvalidArgumentException('Pagination values are outside the supported range.');
        }

        if (strlen($this->keyword) > 100) {
            throw new InvalidArgumentException('Search keywords may not exceed 100 characters.');
        }

        if (
            ($this->minimumPrice !== null && $this->minimumPrice < 0)
            || ($this->maximumPrice !== null && $this->maximumPrice < 0)
            || ($this->minimumDuration !== null && $this->minimumDuration < 0)
            || ($this->maximumDuration !== null && $this->maximumDuration < 0)
        ) {
            throw new InvalidArgumentException('Range filters cannot contain negative values.');
        }

        if (
            ($this->minimumPrice !== null && $this->maximumPrice !== null && $this->minimumPrice > $this->maximumPrice)
            || (
                $this->minimumDuration !== null
                && $this->maximumDuration !== null
                && $this->minimumDuration > $this->maximumDuration
            )
        ) {
            throw new InvalidArgumentException('Range minimums cannot exceed their maximums.');
        }
    }

    /** @return array<string, mixed> */
    public function normalizedState(): array
    {
        $taxonomies = $this->taxonomyFilters;
        $relationships = $this->relationshipFilters;
        ksort($taxonomies);
        ksort($relationships);

        return [
            'context' => $this->context->value,
            'types' => array_map(static fn(ContentType $type): string => $type->value, $this->contentTypes),
            'keyword' => $this->keyword,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort' => $this->sort->value,
            'pagination' => $this->pagination->value,
            'taxonomies' => $taxonomies,
            'relationships' => $relationships,
            'minimum_price' => $this->minimumPrice,
            'maximum_price' => $this->maximumPrice,
            'minimum_duration' => $this->minimumDuration,
            'maximum_duration' => $this->maximumDuration,
        ];
    }
}
