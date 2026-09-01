<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Plugin;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/src/Support/Autoloader.php';
Autoloader::register();

$pluginFile = file_get_contents($projectRoot . '/ads-tourism.php');
$readmeFile = file_get_contents($projectRoot . '/readme.txt');

if ($pluginFile === false || $readmeFile === false) {
    fwrite(STDERR, "Unable to read the plugin version files.\n");
    exit(1);
}

preg_match('/^\s*\*\s*Version:\s*(\S+)/m', $pluginFile, $pluginHeaderMatch);
preg_match('/^Stable tag:\s*(\S+)/m', $readmeFile, $readmeMatch);

$versions = [
    'Plugin::VERSION' => Plugin::VERSION,
    'plugin header' => $pluginHeaderMatch[1] ?? null,
    'readme stable tag' => $readmeMatch[1] ?? null,
];

$expectedVersion = Plugin::VERSION;
$tag = $argv[1] ?? null;

if ($tag === null && getenv('GITHUB_REF_TYPE') === 'tag') {
    $tag = getenv('GITHUB_REF_NAME') ?: null;
}

if (is_string($tag) && $tag !== '') {
    $versions['Git tag'] = ltrim($tag, 'v');
}

foreach ($versions as $source => $version) {
    if ($version !== $expectedVersion) {
        fwrite(
            STDERR,
            sprintf(
                "%s reports version %s; expected %s.\n",
                $source,
                var_export($version, true),
                $expectedVersion,
            ),
        );
        exit(1);
    }
}

fwrite(STDOUT, sprintf("Version %s is consistent.\n", $expectedVersion));
