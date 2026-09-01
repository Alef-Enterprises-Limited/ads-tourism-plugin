<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;

final class PublicQueryCache
{
    private const GROUP = 'ads_tourism_queries';

    private const OPTION_GENERATION = 'ads_tourism_query_cache_generation';

    public function get(TourismQuery $query): ?QueryResult
    {
        $key = $this->key($query);
        $found = false;
        $cached = wp_cache_get($key, self::GROUP, false, $found);

        if (!$found) {
            $cached = get_transient($key);
        }

        if (!is_array($cached)) {
            return null;
        }

        $postIds = isset($cached['post_ids']) && is_array($cached['post_ids'])
            ? array_values(array_filter(array_map('absint', $cached['post_ids'])))
            : [];
        /** @var list<positive-int> $postIds */

        return new QueryResult(
            $postIds,
            absint($cached['total'] ?? 0),
            absint($cached['total_pages'] ?? 0),
            max(1, absint($cached['page'] ?? 1)),
            max(1, absint($cached['per_page'] ?? $query->perPage)),
        );
    }

    public function set(TourismQuery $query, QueryResult $result): void
    {
        $key = $this->key($query);
        $ttl = $query->sort === QuerySort::RANDOM ? MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS;
        $value = [
            'post_ids' => $result->postIds,
            'total' => $result->total,
            'total_pages' => $result->totalPages,
            'page' => $result->page,
            'per_page' => $result->perPage,
        ];
        wp_cache_set($key, $value, self::GROUP, $ttl);
        set_transient($key, $value, $ttl);
    }

    public function invalidate(): void
    {
        $generation = absint(get_option(self::OPTION_GENERATION, 1));
        update_option(self::OPTION_GENERATION, $generation + 1, false);
    }

    private function key(TourismQuery $query): string
    {
        $payload = wp_json_encode([
            'generation' => absint(get_option(self::OPTION_GENERATION, 1)),
            'locale' => determine_locale(),
            'query' => $query->normalizedState(),
        ]);

        return 'ads_tourism_query_' . hash('sha256', is_string($payload) ? $payload : '');
    }
}
