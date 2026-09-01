<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

final class ShortcodeDiagnostic
{
    public function render(string $message): string
    {
        if (!current_user_can('edit_posts')) {
            return '<!-- ADS Tourism shortcode configuration error. -->';
        }

        return '<div class="ads-tourism-diagnostic" role="alert"><strong>'
            . esc_html__('ADS Tourism:', 'ads-tourism') . '</strong> ' . esc_html($message) . '</div>';
    }
}
