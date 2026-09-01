<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use AlefDigitalSolutions\ADSTourism\Domain\Query\PaginationMode;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use InvalidArgumentException;

final class TourismQueryFactory
{
    /** @param array<string, mixed> $input */
    public function create(array $input): TourismQuery
    {
        $context = new ContextName($this->string($input, 'context'));
        $types = $this->contentTypes($input['type'] ?? 'all');
        $page = $this->boundedInteger($input['page'] ?? 1, 1, PHP_INT_MAX, 'page');
        $perPage = $this->boundedInteger(
            $input['per_page'] ?? 12,
            1,
            TourismQuery::MAX_PER_PAGE,
            'per_page',
        );
        $keyword = trim(strip_tags($this->string($input, 'query', '')));

        if (strlen($keyword) > 100) {
            throw new InvalidArgumentException('Search keywords may not exceed 100 characters.');
        }

        return new TourismQuery(
            $context,
            $types,
            $keyword,
            $page,
            $perPage,
            QuerySort::tryFrom($this->string($input, 'sort', QuerySort::TITLE_ASC->value))
                ?? throw new InvalidArgumentException('The requested sorting option is not supported.'),
            PaginationMode::tryFrom($this->string($input, 'pagination', PaginationMode::NUMBERED->value))
                ?? throw new InvalidArgumentException('The requested pagination mode is not supported.'),
            $this->taxonomyFilters($input['taxonomies'] ?? []),
            $this->relationshipFilters($input['relationships'] ?? []),
            $this->nullableNumber($input['minimum_price'] ?? null, 'minimum_price'),
            $this->nullableNumber($input['maximum_price'] ?? null, 'maximum_price'),
            $this->nullableInteger($input['minimum_duration'] ?? null, 'minimum_duration'),
            $this->nullableInteger($input['maximum_duration'] ?? null, 'maximum_duration'),
        );
    }

    /** @return non-empty-list<ContentType> */
    private function contentTypes(mixed $value): array
    {
        $aliases = [
            'place' => ContentType::PLACE,
            'places' => ContentType::PLACE,
            'activity' => ContentType::ACTIVITY,
            'activities' => ContentType::ACTIVITY,
            'stay' => ContentType::STAY,
            'stays' => ContentType::STAY,
            'operator' => ContentType::OPERATOR,
            'operators' => ContentType::OPERATOR,
            'package' => ContentType::PACKAGE,
            'packages' => ContentType::PACKAGE,
        ];
        $requested = is_array($value) ? $value : explode(',', (string) $value);

        if (in_array('all', $requested, true)) {
            return ContentType::cases();
        }

        $types = [];

        foreach ($requested as $item) {
            $key = strtolower(trim((string) $item));
            $type = ContentType::tryFrom($key) ?? ($aliases[$key] ?? null);

            if ($type === null) {
                throw new InvalidArgumentException('The requested tourism content type is not supported.');
            }

            $types[$type->value] = $type;
        }

        if ($types === []) {
            throw new InvalidArgumentException('At least one tourism content type is required.');
        }

        return array_values($types);
    }

    /** @return array<string, list<string>> */
    private function taxonomyFilters(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Taxonomy filters must be an object.');
        }

        $allowed = array_map(
            static fn(TourismTaxonomy $taxonomy): string => $taxonomy->value,
            TourismTaxonomy::cases(),
        );
        $filters = [];

        foreach ($value as $taxonomy => $terms) {
            if (!is_string($taxonomy) || !in_array($taxonomy, $allowed, true)) {
                throw new InvalidArgumentException('A taxonomy filter is not supported.');
            }

            $termValues = is_array($terms) ? $terms : explode(',', (string) $terms);
            $slugs = [];

            foreach ($termValues as $term) {
                $slug = strtolower(trim((string) $term));

                if ($slug === '' || preg_match('/^[a-z0-9_-]+$/', $slug) !== 1) {
                    throw new InvalidArgumentException('Taxonomy filters must contain valid term slugs.');
                }

                $slugs[] = $slug;
            }

            $filters[$taxonomy] = array_values(array_unique($slugs));
        }

        return $filters;
    }

    /** @return array<string, positive-int> */
    private function relationshipFilters(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Relationship filters must be an object.');
        }

        $allowed = ['place', 'activity', 'stay', 'operator', 'package'];
        $filters = [];

        foreach ($value as $type => $postId) {
            if (!is_string($type) || !in_array($type, $allowed, true)) {
                throw new InvalidArgumentException('A relationship filter is not supported.');
            }

            $id = filter_var($postId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if (!is_int($id)) {
                throw new InvalidArgumentException('Relationship filters require positive record IDs.');
            }

            $filters[$type] = $id;
        }

        return $filters;
    }

    /** @param array<string, mixed> $input */
    private function string(array $input, string $key, string $default = ''): string
    {
        $value = $input[$key] ?? $default;

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('%s must be a string.', $key));
        }

        return (string) $value;
    }

    private function boundedInteger(mixed $value, int $minimum, int $maximum, string $name): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if (!is_int($integer) || $integer < $minimum || $integer > $maximum) {
            throw new InvalidArgumentException(sprintf('%s is outside the supported range.', $name));
        }

        return $integer;
    }

    private function nullableInteger(mixed $value, string $name): ?int
    {
        return $value === null || $value === '' ? null : $this->boundedInteger($value, 0, PHP_INT_MAX, $name);
    }

    private function nullableNumber(mixed $value, string $name): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException(sprintf('%s must be a non-negative number.', $name));
        }

        return (float) $value;
    }
}
