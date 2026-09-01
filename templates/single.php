<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;

get_header();

while (have_posts()) {
    the_post();
    $renderer = apply_filters('ads_tourism_frontend_renderer', null);

    if ($renderer instanceof FrontendRenderer) {
        $renderer->renderSingle(get_the_ID());
    }
}

get_footer();
