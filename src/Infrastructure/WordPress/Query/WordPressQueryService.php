<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationshipRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use WP_Post;
use WP_Query;

final readonly class WordPressQueryService
{
    public function __construct(
        private RelationshipRepository $relationships,
        private PublicQueryCache $cache,
    ) {}

    public function execute(TourismQuery $query): QueryResult
    {
        $cached = $this->cache->get($query);

        if ($cached !== null) {
            return $cached;
        }

        $arguments = [
            'post_type' => array_map(static fn(ContentType $type): string => $type->value, $query->contentTypes),
            'post_status' => 'publish',
            'posts_per_page' => $query->perPage,
            'paged' => $query->page,
            's' => $query->keyword,
            'ignore_sticky_posts' => true,
            'no_found_rows' => false,
            'fields' => 'ids',
        ];
        $this->applySorting($arguments, $query->sort);
        $this->applyTaxonomies($arguments, $query->taxonomyFilters);
        $this->applyRanges($arguments, $query);
        $relatedIds = $this->relationshipPostIds($query);

        if ($relatedIds !== null) {
            $arguments['post__in'] = $relatedIds === [] ? [0] : $relatedIds;
        }

        $wordpressQuery = new WP_Query($arguments);
        $postIds = array_values(array_filter(array_map(
            static fn(mixed $post): int => $post instanceof WP_Post ? $post->ID : absint($post),
            $wordpressQuery->posts,
        ), static fn(int $postId): bool => $postId > 0));
        /** @var list<positive-int> $postIds */

        if ($postIds !== [] && function_exists('_prime_post_caches')) {
            _prime_post_caches($postIds, true, true);
        }

        $result = new QueryResult(
            $postIds,
            absint($wordpressQuery->found_posts),
            absint($wordpressQuery->max_num_pages),
            $query->page,
            $query->perPage,
        );
        $this->cache->set($query, $result);

        return $result;
    }

    /** @param array<string, mixed> $arguments */
    private function applySorting(array &$arguments, QuerySort $sort): void
    {
        [$orderBy, $order, $metaKey] = match ($sort) {
            QuerySort::TITLE_ASC => ['title', 'ASC', null],
            QuerySort::TITLE_DESC => ['title', 'DESC', null],
            QuerySort::NEWEST => ['date', 'DESC', null],
            QuerySort::OLDEST => ['date', 'ASC', null],
            QuerySort::MANUAL => ['meta_value_num', 'ASC', 'ads_tourism_manual_order'],
            QuerySort::PRICE_ASC => ['meta_value_num', 'ASC', 'ads_tourism_price_from'],
            QuerySort::PRICE_DESC => ['meta_value_num', 'DESC', 'ads_tourism_price_from'],
            QuerySort::DURATION => ['meta_value_num', 'ASC', 'ads_tourism_duration_days'],
            QuerySort::RANDOM => ['rand', '', null],
        };
        $arguments['orderby'] = $orderBy;

        if ($order !== '') {
            $arguments['order'] = $order;
        }

        if ($metaKey !== null) {
            $arguments['meta_key'] = $metaKey;
        }
    }

    /**
     * @param array<string, mixed>         $arguments
     * @param array<string, list<string>> $filters
     */
    private function applyTaxonomies(array &$arguments, array $filters): void
    {
        if ($filters === []) {
            return;
        }

        $taxQuery = ['relation' => 'AND'];

        foreach ($filters as $taxonomy => $slugs) {
            $taxQuery[] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $slugs,
                'operator' => 'IN',
            ];
        }

        $arguments['tax_query'] = $taxQuery;
    }

    /** @param array<string, mixed> $arguments */
    private function applyRanges(array &$arguments, TourismQuery $query): void
    {
        $metaQuery = ['relation' => 'AND'];
        $this->addRange($metaQuery, 'ads_tourism_price_from', $query->minimumPrice, $query->maximumPrice, 'DECIMAL');
        $this->addRange(
            $metaQuery,
            'ads_tourism_duration_days',
            $query->minimumDuration,
            $query->maximumDuration,
            'NUMERIC',
        );

        if (count($metaQuery) > 1) {
            $arguments['meta_query'] = $metaQuery;
        }
    }

    /**
     * @param array<int|string, mixed> $metaQuery
     */
    private function addRange(
        array &$metaQuery,
        string $key,
        float|int|null $minimum,
        float|int|null $maximum,
        string $type,
    ): void {
        if ($minimum === null && $maximum === null) {
            return;
        }

        if ($minimum !== null && $maximum !== null) {
            $metaQuery[] = ['key' => $key, 'value' => [$minimum, $maximum], 'compare' => 'BETWEEN', 'type' => $type];

            return;
        }

        $metaQuery[] = [
            'key' => $key,
            'value' => $minimum ?? $maximum,
            'compare' => $minimum !== null ? '>=' : '<=',
            'type' => $type,
        ];
    }

    /** @return list<int>|null */
    private function relationshipPostIds(TourismQuery $query): ?array
    {
        if ($query->relationshipFilters === []) {
            return null;
        }

        $allowedTypes = array_map(static fn(ContentType $type): string => $type->value, $query->contentTypes);
        $intersection = null;

        foreach ($query->relationshipFilters as $recordId) {
            $selectedType = ContentType::tryFrom((string) get_post_type($recordId));
            $matching = [];

            if ($selectedType === null) {
                return [];
            }

            foreach (RelationType::forContentType($selectedType) as $relationType) {
                $side = $relationType->sideFor($selectedType);

                if ($side === null) {
                    continue;
                }

                foreach ($this->relationships->findForRecord($recordId, $relationType, $side) as $relationship) {
                    $relatedId = $relationship->relatedPostId($side);

                    if (in_array((string) get_post_type($relatedId), $allowedTypes, true)) {
                        $matching[] = $relatedId;
                    }
                }
            }

            $matching = array_values(array_unique($matching));
            $intersection = $intersection === null ? $matching : array_values(array_intersect($intersection, $matching));
        }

        return $intersection ?? [];
    }
}
