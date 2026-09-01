<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

use AlefDigitalSolutions\ADSTourism\Domain\Query\PaginationMode;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use AlefDigitalSolutions\ADSTourism\Domain\Query\TourismQuery;

final class PaginationRenderer
{
    public function render(QueryResult $result, TourismQuery $query, string $baseUrl): string
    {
        if ($query->pagination === PaginationMode::NONE || $result->totalPages <= 1) {
            return '';
        }

        $previous = max(1, $result->page - 1);
        $next = min($result->totalPages, $result->page + 1);
        $html = '<nav class="ads-tourism-pagination" aria-label="'
            . esc_attr__('Tourism results pages', 'ads-tourism') . '" data-ads-tourism-pagination>';

        if ($query->pagination === PaginationMode::LOAD_MORE || $query->pagination === PaginationMode::INFINITE) {
            if ($result->page < $result->totalPages) {
                $html .= $this->link($query, $baseUrl, $next, __('Load more', 'ads-tourism'), 'next');
            }

            return $html . '</nav>';
        }

        if ($result->page > 1) {
            $html .= $this->link($query, $baseUrl, $previous, __('Previous', 'ads-tourism'), 'prev');
        }

        if ($query->pagination === PaginationMode::NUMBERED) {
            foreach ($this->pageNumbers($result->page, $result->totalPages) as $page) {
                if ($page === null) {
                    $html .= '<span class="ads-tourism-pagination__ellipsis" aria-hidden="true">…</span>';
                } elseif ($page === $result->page) {
                    $html .= '<span class="ads-tourism-pagination__current" aria-current="page">';
                    $html .= esc_html((string) $page) . '</span>';
                } else {
                    $html .= $this->link($query, $baseUrl, $page, (string) $page);
                }
            }
        }

        if ($result->page < $result->totalPages) {
            $html .= $this->link($query, $baseUrl, $next, __('Next', 'ads-tourism'), 'next');
        }

        return $html . '</nav>';
    }

    /** @return list<int|null> */
    private function pageNumbers(int $current, int $total): array
    {
        $pages = array_values(array_unique(array_filter([
            1,
            $current - 2,
            $current - 1,
            $current,
            $current + 1,
            $current + 2,
            $total,
        ], static fn(int $page): bool => $page >= 1 && $page <= $total)));
        sort($pages);
        $output = [];
        $previous = 0;

        foreach ($pages as $page) {
            if ($previous > 0 && $page > $previous + 1) {
                $output[] = null;
            }

            $output[] = $page;
            $previous = $page;
        }

        return $output;
    }

    private function link(
        TourismQuery $query,
        string $baseUrl,
        int $page,
        string $label,
        string $relation = '',
    ): string {
        $url = add_query_arg($query->context->parameter('page'), $page, $baseUrl);
        $rel = $relation === '' ? '' : ' rel="' . esc_attr($relation) . '"';

        return '<a class="ads-tourism-pagination__link" href="' . esc_url($url) . '" data-page="'
            . esc_attr((string) $page) . '"' . $rel . '>' . esc_html($label) . '</a>';
    }
}
