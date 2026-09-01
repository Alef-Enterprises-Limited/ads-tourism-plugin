<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

use AlefDigitalSolutions\ADSTourism\Domain\Query\QueryResult;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;

final readonly class ListingRenderer
{
    public function __construct(private FrontendRenderer $records) {}

    public function render(QueryResult $result, string $context, int $columns = 3): string
    {
        ob_start();
        echo '<div class="ads-tourism-results" data-ads-tourism-results tabindex="-1">';

        if ($result->postIds === []) {
            echo '<div class="ads-tourism-empty" role="status">';
            echo '<p>' . esc_html__('No tourism records match these filters.', 'ads-tourism') . '</p></div>';
        } else {
            echo '<div class="ads-tourism-grid" role="list" style="--ads-tourism-listing-columns:';
            echo esc_attr((string) min(6, max(1, $columns))) . '">';

            foreach ($result->postIds as $postId) {
                $this->records->renderCard($postId);
            }

            echo '</div>';
        }

        echo '<p class="screen-reader-text ads-tourism-results__status" aria-live="polite" aria-atomic="true">';
        echo esc_html(sprintf(
            _n('%d tourism record found.', '%d tourism records found.', $result->total, 'ads-tourism'),
            $result->total,
        ));
        echo '</p></div>';

        return (string) ob_get_clean();
    }
}
