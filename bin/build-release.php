<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Plugin;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/src/Support/Autoloader.php';
Autoloader::register();

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "The PHP zip extension is required to build a release.\n");
    exit(1);
}

$buildDirectory = $projectRoot . '/build';
$archivePath = $buildDirectory . '/ads-tourism-' . Plugin::VERSION . '.zip';

if (!is_dir($buildDirectory) && !mkdir($buildDirectory, 0755, true) && !is_dir($buildDirectory)) {
    fwrite(STDERR, "Unable to create the build directory.\n");
    exit(1);
}

$excludedTopLevelPaths = [
    '.git',
    '.github',
    'bin',
    'build',
    'docs',
    'tests',
    'vendor',
];

$excludedFiles = [
    '.editorconfig',
    '.gitattributes',
    '.gitignore',
    '.php-cs-fixer.dist.php',
    'AGENTS.md',
    'CONTRIBUTING.md',
    'composer.json',
    'composer.lock',
    'phpstan.neon.dist',
    'phpunit.xml.dist',
];

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($projectRoot) + 1));
    $topLevelPath = strtok($relativePath, '/');

    if (in_array($topLevelPath, $excludedTopLevelPaths, true)) {
        continue;
    }

    if (in_array($relativePath, $excludedFiles, true)) {
        continue;
    }

    $files[$relativePath] = $file->getPathname();
}

ksort($files);

$archive = new ZipArchive();
$openResult = $archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($openResult !== true) {
    fwrite(STDERR, sprintf("Unable to open release archive; ZipArchive returned %s.\n", $openResult));
    exit(1);
}

foreach ($files as $relativePath => $sourcePath) {
    if (!$archive->addFile($sourcePath, 'ads-tourism/' . $relativePath)) {
        $archive->close();
        fwrite(STDERR, sprintf("Unable to add %s to the release archive.\n", $relativePath));
        exit(1);
    }
}

if (!$archive->close()) {
    fwrite(STDERR, "Unable to finalize the release archive.\n");
    exit(1);
}

fwrite(STDOUT, $archivePath . PHP_EOL);
