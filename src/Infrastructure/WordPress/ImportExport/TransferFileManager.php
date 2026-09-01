<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use RuntimeException;

final readonly class TransferFileManager
{
    public function __construct(private TransferSettings $settings) {}

    /**
     * @param array<string, mixed> $file
     *
     * @return array{path: string, filename: string}
     */
    public function storeUpload(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $size = (int) ($file['size'] ?? 0);
        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $filename = sanitize_file_name((string) ($file['name'] ?? ''));

        if ($error !== UPLOAD_ERR_OK || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('The CSV upload did not complete successfully.');
        }

        if ($size <= 0 || $size > $this->settings->maximumBytes()) {
            throw new RuntimeException('The CSV file exceeds the configured size limit or is empty.');
        }

        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'csv') {
            throw new RuntimeException('Only .csv files are accepted.');
        }

        $mime = $this->mimeType($temporaryPath);

        if (!in_array($mime, ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'], true)) {
            throw new RuntimeException('The uploaded file does not have an accepted CSV MIME type.');
        }

        $sample = file_get_contents($temporaryPath, false, null, 0, 65536);

        if (!is_string($sample) || preg_match('//u', $sample) !== 1) {
            throw new RuntimeException('CSV files must use UTF-8 encoding.');
        }

        $directory = $this->baseDirectory();
        $path = $directory . '/import-' . wp_generate_uuid4() . '.csv';

        if (!move_uploaded_file($temporaryPath, $path)) {
            throw new RuntimeException('The CSV upload could not be moved into protected storage.');
        }

        chmod($path, 0600);

        return ['path' => $path, 'filename' => $filename];
    }

    public function rejectedPath(int $runId): string
    {
        return $this->baseDirectory() . '/rejected-' . $runId . '-' . wp_generate_uuid4() . '.csv';
    }

    public function delete(string $path): void
    {
        if ($this->isManagedPath($path) && is_file($path)) {
            unlink($path);
        }
    }

    public function cleanupExpired(): void
    {
        $cutoff = time() - $this->settings->retentionSeconds();

        foreach (glob($this->baseDirectory() . '/*') ?: [] as $path) {
            $modified = filemtime($path);

            if (basename($path) !== 'index.php' && is_file($path) && is_int($modified) && $modified < $cutoff) {
                unlink($path);
            }
        }
    }

    public function isManagedPath(string $path): bool
    {
        $base = realpath($this->baseDirectory());
        $resolved = realpath($path);

        return is_string($base)
            && is_string($resolved)
            && str_starts_with($resolved, $base . DIRECTORY_SEPARATOR);
    }

    private function baseDirectory(): string
    {
        $uploads = wp_upload_dir();
        $base = rtrim((string) ($uploads['basedir'] ?? ''), '/');

        if ($base === '') {
            throw new RuntimeException('The WordPress uploads directory is unavailable.');
        }

        $directory = $base . '/ads-tourism-transfers';

        if (!is_dir($directory) && !wp_mkdir_p($directory)) {
            throw new RuntimeException('The protected transfer directory could not be created.');
        }

        file_put_contents($directory . '/index.php', "<?php\n// Silence is golden.\n");
        file_put_contents($directory . '/.htaccess', "Require all denied\n");

        return $directory;
    }

    private function mimeType(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return 'text/plain';
        }

        $handle = finfo_open(FILEINFO_MIME_TYPE);

        if ($handle === false) {
            return 'text/plain';
        }

        $mime = finfo_file($handle, $path);
        finfo_close($handle);

        return is_string($mime) ? $mime : '';
    }
}
