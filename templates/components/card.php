<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;

if (!isset($postId, $renderer) || !is_int($postId) || !$renderer instanceof FrontendRenderer) {
    return;
}

$url = get_permalink($postId);
$summary = apply_filters('ads_tourism_resolved_field', null, $postId, 'ads_tourism_summary');
ob_start();
$renderer->renderFeaturedMedia($postId, 'medium_large', 'ads-tourism-card__media');
$mediaMarkup = (string) ob_get_clean();

echo '<article class="ads-tourism-card" role="listitem">';

if (is_string($url) && $mediaMarkup !== '') {
    echo '<a class="ads-tourism-card__media-link" href="' . esc_url($url) . '" tabindex="-1" aria-hidden="true">';
    echo $mediaMarkup;
    echo '</a>';
} elseif ($mediaMarkup !== '') {
    echo $mediaMarkup;
}

echo '<div class="ads-tourism-card__body"><h2 class="ads-tourism-card__title">';

if (is_string($url)) {
    echo '<a href="' . esc_url($url) . '">' . esc_html(get_the_title($postId)) . '</a>';
} else {
    echo esc_html(get_the_title($postId));
}

echo '</h2>';

if (is_string($summary) && trim($summary) !== '') {
    echo '<p class="ads-tourism-card__summary">' . esc_html(wp_trim_words($summary, 30)) . '</p>';
}

echo '</div></article>';
