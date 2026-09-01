<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Plugin;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/src/Support/Autoloader.php';
Autoloader::register();

$potCheckCommand = escapeshellarg(PHP_BINARY)
    . ' '
    . escapeshellarg($projectRoot . '/bin/generate-pot.php')
    . ' --check';
passthru($potCheckCommand, $potCheckStatus);

if ($potCheckStatus !== 0) {
    fwrite(STDERR, "Generated translation assets must be current before packaging.\n");
    exit(1);
}

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "The PHP zip extension is required to build a release.\n");
    exit(1);
}

$requestedVersion = $argv[1] ?? Plugin::VERSION;

if ($requestedVersion !== Plugin::VERSION || preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/', $requestedVersion) !== 1) {
    fwrite(STDERR, "The requested release version does not match the plugin version.\n");
    exit(1);
}

$buildDirectory = $projectRoot . '/build';
$artifactBase = 'ads-tourism-' . $requestedVersion;
$archivePath = $buildDirectory . '/' . $artifactBase . '.zip';
$checksumPath = $archivePath . '.sha256';
$manifestPath = $buildDirectory . '/' . $artifactBase . '-manifest.json';

if (!is_dir($buildDirectory) && !mkdir($buildDirectory, 0755, true) && !is_dir($buildDirectory)) {
    fwrite(STDERR, "Unable to create the build directory.\n");
    exit(1);
}

$allowedDirectories = [
    'assets',
    'languages',
    'src',
    'templates',
];

$allowedFiles = [
    'ads-tourism.php',
    'CHANGELOG.md',
    'LICENSE',
    'README.md',
    'readme.txt',
    'SECURITY.md',
    'uninstall.php',
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

    if (!in_array($topLevelPath, $allowedDirectories, true) && !in_array($relativePath, $allowedFiles, true)) {
        continue;
    }

    $files[$relativePath] = [
        'source' => $file->getPathname(),
        'size' => $file->getSize(),
        'sha256' => hash_file('sha256', $file->getPathname()),
    ];
}

ksort($files);

if ($files === []) {
    fwrite(STDERR, "No production files were selected for the release.\n");
    exit(1);
}

$manifest = [
    'artifact' => basename($archivePath),
    'format_version' => 1,
    'plugin' => 'ADS Tourism',
    'requires_php' => '8.2',
    'requires_wordpress' => '6.6',
    'root_directory' => 'ads-tourism',
    'version' => $requestedVersion,
    'files' => array_map(
        static fn(array $file): array => ['size' => $file['size'], 'sha256' => $file['sha256']],
        $files,
    ),
];
$encodedManifest = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (!is_string($encodedManifest) || file_put_contents($manifestPath, $encodedManifest . PHP_EOL) === false) {
    fwrite(STDERR, "Unable to write the release manifest.\n");
    exit(1);
}

$archive = new ZipArchive();
$openResult = $archive->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($openResult !== true) {
    fwrite(STDERR, sprintf("Unable to open release archive; ZipArchive returned %s.\n", $openResult));
    exit(1);
}

$archive->addEmptyDir('ads-tourism');
$sourceDateEpoch = getenv('SOURCE_DATE_EPOCH');
$archiveTimestamp = is_string($sourceDateEpoch) && ctype_digit($sourceDateEpoch)
    ? max(315532800, (int) $sourceDateEpoch)
    : 946684800;

foreach ($files as $relativePath => $file) {
    $archiveName = 'ads-tourism/' . $relativePath;

    if (!$archive->addFile($file['source'], $archiveName)) {
        $archive->close();
        fwrite(STDERR, sprintf("Unable to add %s to the release archive.\n", $relativePath));
        exit(1);
    }

    if (method_exists($archive, 'setMtimeName')) {
        $archive->setMtimeName($archiveName, $archiveTimestamp);
    }
}

if (!$archive->close()) {
    fwrite(STDERR, "Unable to finalize the release archive.\n");
    exit(1);
}

$archiveHash = hash_file('sha256', $archivePath);

if (!is_string($archiveHash) || file_put_contents(
    $checksumPath,
    $archiveHash . '  ' . basename($archivePath) . PHP_EOL,
) === false) {
    fwrite(STDERR, "Unable to write the release checksum.\n");
    exit(1);
}

fwrite(STDOUT, $archivePath . PHP_EOL);
fwrite(STDOUT, $checksumPath . PHP_EOL);
fwrite(STDOUT, $manifestPath . PHP_EOL);
