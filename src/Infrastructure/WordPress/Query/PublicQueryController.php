<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Query;

use AlefDigitalSolutions\ADSTourism\Application\Query\TourismQueryFactory;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ListingRenderer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\PaginationRenderer;
use InvalidArgumentException;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final readonly class PublicQueryController
{
    public const NAMESPACE = 'ads-tourism/v1';

    public const ROUTE = '/query';

    public function __construct(
        private TourismQueryFactory $queries,
        private WordPressQueryService $queryService,
        private ListingRenderer $listings,
        private PaginationRenderer $pagination,
    ) {}

    public function register(): void
    {
        register_rest_route(self::NAMESPACE, self::ROUTE, [
            'methods' => 'GET',
            'callback' => [$this, 'query'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function query(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        try {
            $input = $request->get_params();
            $input['taxonomies'] = $this->objectParameter($request->get_param('taxonomies'));
            $input['relationships'] = $this->objectParameter($request->get_param('relationships'));
            $query = $this->queries->create($input);
            $result = $this->queryService->execute($query);
            $columns = $this->columns($request->get_param('columns'));

            return rest_ensure_response([
                'context' => $query->context->value,
                'html' => $this->listings->render($result, $query->context->value, $columns),
                'pagination_html' => $this->pagination->render($result, $query, home_url('/')),
                'total' => $result->total,
                'total_pages' => $result->totalPages,
                'page' => $result->page,
                'state' => $query->normalizedState(),
            ]);
        } catch (InvalidArgumentException $exception) {
            return new WP_Error('ads_tourism_invalid_query', $exception->getMessage(), ['status' => 400]);
        }
    }

    /** @return array<string, mixed> */
    private function objectParameter(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (!is_array($value) && !is_string($value)) {
            throw new InvalidArgumentException('Query filters must be JSON objects.');
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('Query filters must be valid JSON objects.');
        }

        $object = [];

        foreach ($decoded as $key => $item) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Query filters must be JSON objects.');
            }

            $object[$key] = $item;
        }

        return $object;
    }

    private function columns(mixed $value): int
    {
        $columns = filter_var($value ?? 3, FILTER_VALIDATE_INT);

        return is_int($columns) ? min(6, max(1, $columns)) : 3;
    }
}
