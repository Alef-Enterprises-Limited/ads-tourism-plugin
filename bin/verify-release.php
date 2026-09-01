<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Plugin;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/src/Support/Autoloader.php';
Autoloader::register();

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "The PHP zip extension is required to verify a release.\n");
    exit(1);
}

$artifactBase = 'ads-tourism-' . Plugin::VERSION;
$archivePath = $projectRoot . '/build/' . $artifactBase . '.zip';
$checksumPath = $archivePath . '.sha256';
$manifestPath = $projectRoot . '/build/' . $artifactBase . '-manifest.json';

foreach ([$archivePath, $checksumPath, $manifestPath] as $requiredFile) {
    if (!is_file($requiredFile)) {
        fwrite(STDERR, sprintf("Required release artifact is missing: %s\n", basename($requiredFile)));
        exit(1);
    }
}

$checksum = trim((string) file_get_contents($checksumPath));
$archiveHash = hash_file('sha256', $archivePath);

if (!is_string($archiveHash) || $checksum !== $archiveHash . '  ' . basename($archivePath)) {
    fwrite(STDERR, "The release checksum does not match the ZIP.\n");
    exit(1);
}

$manifest = json_decode((string) file_get_contents($manifestPath), true);

if (!is_array($manifest) || ($manifest['version'] ?? null) !== Plugin::VERSION || !is_array($manifest['files'] ?? null)) {
    fwrite(STDERR, "The release manifest is invalid.\n");
    exit(1);
}

$archive = new ZipArchive();

if ($archive->open($archivePath) !== true) {
    fwrite(STDERR, "The release ZIP could not be opened.\n");
    exit(1);
}

$entries = [];

for ($index = 0; $index < $archive->numFiles; ++$index) {
    $name = $archive->getNameIndex($index);

    if (!is_string($name) || ($name !== 'ads-tourism/' && !str_starts_with($name, 'ads-tourism/'))) {
        $archive->close();
        fwrite(STDERR, "The ZIP contains a file outside the ads-tourism root directory.\n");
        exit(1);
    }

    if ($name !== 'ads-tourism/') {
        $entries[] = substr($name, strlen('ads-tourism/'));
    }
}

$archive->close();
$manifestEntries = array_keys($manifest['files']);
sort($entries);
sort($manifestEntries);

if ($entries !== $manifestEntries) {
    fwrite(STDERR, "The release manifest does not match the ZIP contents.\n");
    exit(1);
}

$forbiddenPrefixes = ['.git', '.github', 'bin/', 'build/', 'docs/', 'tests/', 'vendor/'];

foreach ($entries as $entry) {
    foreach ($forbiddenPrefixes as $prefix) {
        if ($entry === $prefix || str_starts_with($entry, $prefix)) {
            fwrite(STDERR, sprintf("Development path leaked into release: %s\n", $entry));
            exit(1);
        }
    }
}

foreach (['ads-tourism.php', 'uninstall.php', 'src/Plugin.php', 'languages/ads-tourism.pot'] as $requiredEntry) {
    if (!in_array($requiredEntry, $entries, true)) {
        fwrite(STDERR, sprintf("Required plugin file is absent from release: %s\n", $requiredEntry));
        exit(1);
    }
}

fwrite(STDOUT, sprintf(
    "Release %s verified: %d files, SHA-256 %s.\n",
    Plugin::VERSION,
    count($entries),
    $archiveHash,
));
