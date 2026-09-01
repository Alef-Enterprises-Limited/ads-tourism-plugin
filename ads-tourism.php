<?php

/**
 * Plugin Name: ADS Tourism
 * Plugin URI: https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin
 * Description: WordPress-native tourism content for destinations, activities, accommodation, operators, and packages.
 * Version: 1.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * Author: Alef Digital Solutions
 * Author URI: https://github.com/Alef-Enterprises-Limited
 * License: Apache-2.0
 * License URI: https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain: ads-tourism
 * Domain Path: /languages
 */

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\PluginFactory;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

if (!defined('ABSPATH')) {
    exit;
}

// PHPStan runs on supported PHP versions, but WordPress may load this file on an older server.
// @phpstan-ignore smaller.alwaysFalse
if (PHP_VERSION_ID < 80200) {
    add_action(
        'admin_notices',
        static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('ADS Tourism requires PHP 8.2 or newer. The plugin has not been started.', 'ads-tourism');
            echo '</p></div>';
        },
    );

    return;
}

require_once __DIR__ . '/src/Support/Autoloader.php';

Autoloader::register();

$adsTourismPlugin = PluginFactory::create(__FILE__);
$adsTourismPlugin->register();

register_activation_hook(__FILE__, [$adsTourismPlugin, 'activate']);
register_deactivation_hook(__FILE__, [$adsTourismPlugin, 'deactivate']);
