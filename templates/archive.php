<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendRenderer;

get_header();

$renderer = apply_filters('ads_tourism_frontend_renderer', null);

if ($renderer instanceof FrontendRenderer) {
    $renderer->renderArchive();
}

get_footer();
