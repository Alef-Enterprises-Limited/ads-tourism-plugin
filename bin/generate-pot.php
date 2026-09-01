<?php

declare(strict_types=1);

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Media\MediaRole;
use AlefDigitalSolutions\ADSTourism\Domain\Query\QuerySort;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RelationType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;
use AlefDigitalSolutions\ADSTourism\Support\Autoloader;

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/src/Support/Autoloader.php';
Autoloader::register();

$messages = [];
$translationFunctions = [
    '__',
    '_e',
    '_x',
    'esc_attr__',
    'esc_attr_e',
    'esc_attr_x',
    'esc_html__',
    'esc_html_e',
    'esc_html_x',
];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $source = file_get_contents($path);

    if (!is_string($source)) {
        continue;
    }

    $tokens = token_get_all($source);
    $relativePath = str_replace('\\', '/', substr($path, strlen($projectRoot) + 1));

    foreach ($tokens as $index => $token) {
        if (!is_array($token) || $token[0] !== T_STRING) {
            continue;
        }

        $function = $token[1];

        if ($function === '_n') {
            addPotMessage($messages, literalArgument($tokens, $index, 1), $relativePath, 1);
            addPotMessage($messages, literalArgument($tokens, $index, 2), $relativePath, 1);
        } elseif (in_array($function, $translationFunctions, true)) {
            addPotMessage($messages, literalArgument($tokens, $index, 1), $relativePath, 1);
        }
    }
}

$schema = new RecordFieldSchema();

foreach (ContentType::cases() as $contentType) {
    foreach ($schema->for($contentType) as $field) {
        addPotMessage($messages, $field->label, 'src/Domain/Field/RecordFieldSchema.php', 1);
        addPotMessage($messages, $field->description, 'src/Domain/Field/RecordFieldSchema.php', 1);

        foreach ($field->options as $label) {
            addPotMessage($messages, $label, 'src/Domain/Field/RecordFieldSchema.php', 1);
        }
    }
}

foreach (RelationType::cases() as $relationType) {
    addPotMessage($messages, $relationType->label(), 'src/Domain/Relationship/RelationType.php', 1);

    foreach (ContentType::cases() as $contentType) {
        if ($relationType->sideFor($contentType) !== null) {
            addPotMessage($messages, $relationType->labelFor($contentType), 'src/Domain/Relationship/RelationType.php', 1);
        }
    }
}

foreach ([MediaRole::labels(), QuerySort::labels(), VerificationStatus::labels()] as $labels) {
    foreach ($labels as $label) {
        addPotMessage($messages, $label, 'src/Domain', 1);
    }
}

ksort($messages, SORT_NATURAL | SORT_FLAG_CASE);
$pot = <<<'POT'
msgid ""
msgstr ""
"Project-Id-Version: ADS Tourism 0.1.0\n"
"Report-Msgid-Bugs-To: https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/issues\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"X-Domain: ads-tourism\n"

POT;

foreach ($messages as $message => $references) {
    sort($references);
    $pot .= '#: ' . implode(' ', array_unique($references)) . "\n";
    $pot .= 'msgid "' . potEscape($message) . '"' . "\n";
    $pot .= 'msgstr ""' . "\n\n";
}

$outputPath = $projectRoot . '/languages/ads-tourism.pot';

if (($argv[1] ?? '') === '--check') {
    $current = is_file($outputPath) ? file_get_contents($outputPath) : false;

    if ($current !== $pot) {
        fwrite(STDERR, "languages/ads-tourism.pot is out of date. Run composer make-pot.\n");

        exit(1);
    }

    fwrite(STDOUT, "Translation template is current.\n");
    exit(0);
}

if (!is_dir(dirname($outputPath)) && !mkdir(dirname($outputPath), 0755, true) && !is_dir(dirname($outputPath))) {
    fwrite(STDERR, "Unable to create the languages directory.\n");
    exit(1);
}

if (file_put_contents($outputPath, $pot) === false) {
    fwrite(STDERR, "Unable to write the translation template.\n");
    exit(1);
}

fwrite(STDOUT, $outputPath . PHP_EOL);

/**
 * @param array<string, list<string>> $messages
 */
function addPotMessage(array &$messages, ?string $message, string $path, int $line): void
{
    if ($message === null || trim($message) === '') {
        return;
    }

    $messages[$message] ??= [];
    $messages[$message][] = $path . ':' . $line;
}

/** @param list<array{int, string, int}|string> $tokens */
function literalArgument(array $tokens, int $functionIndex, int $argument): ?string
{
    $depth = 0;
    $currentArgument = 1;

    for ($index = $functionIndex + 1, $count = count($tokens); $index < $count; ++$index) {
        $token = $tokens[$index];
        $text = is_array($token) ? $token[1] : $token;

        if ($text === '(') {
            ++$depth;
            continue;
        }

        if ($text === ')') {
            --$depth;

            if ($depth === 0) {
                return null;
            }

            continue;
        }

        if ($depth === 1 && $text === ',') {
            ++$currentArgument;
            continue;
        }

        if ($depth === 1 && $currentArgument === $argument && is_array($token)) {
            if (in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $token[0] === T_CONSTANT_ENCAPSED_STRING ? decodePhpString($token[1]) : null;
        }
    }

    return null;
}

function decodePhpString(string $literal): string
{
    $quote = $literal[0];
    $value = substr($literal, 1, -1);

    return $quote === "'"
        ? str_replace(["\\\\", "\\'"], ["\\", "'"], $value)
        : stripcslashes($value);
}

function potEscape(string $value): string
{
    return str_replace(
        ["\\", "\"", "\n", "\r", "\t"],
        ["\\\\", "\\\"", "\\n", "\\r", "\\t"],
        $value,
    );
}
