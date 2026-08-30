<?php

declare(strict_types=1);

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Tourism content is preserved by default. A future explicit setting will
// allow site administrators to request destructive cleanup during uninstall.
