<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->exclude(['Unit/FileLanguages', 'Unit/DirLanguages', 'Unit/Fixtures']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                      => true,
        '@PSR12:risky'                => true,
        '@PHP81Migration'             => true,
        'array_syntax'                => ['syntax' => 'short'],
        'declare_strict_types'        => true,
        'native_function_invocation'  => [
            'include' => ['@compiler_optimized'],
            'scope'   => 'namespaced',
            'strict'  => true,
        ],
        'no_unused_imports'           => true,
        'ordered_imports'             => [
            'imports_order'  => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_quote'                => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/build/php-cs-fixer.cache');
