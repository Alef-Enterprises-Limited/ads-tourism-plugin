<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude([
        'build',
        'vendor',
    ])
    ->name('*.php');

return (new Config())
    ->setRules([
        '@PER-CS3x0' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($finder);
