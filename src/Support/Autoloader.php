<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Support;

final class Autoloader
{
    private const NAMESPACE_PREFIX = 'AlefDigitalSolutions\\ADSTourism\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    private static function load(string $className): void
    {
        if (!str_starts_with($className, self::NAMESPACE_PREFIX)) {
            return;
        }

        $relativeClass = substr($className, strlen(self::NAMESPACE_PREFIX));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
        $sourcePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;

        if (is_file($sourcePath)) {
            require_once $sourcePath;
        }
    }
}
