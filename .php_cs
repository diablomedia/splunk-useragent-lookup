<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*_lookup')
    ->in(__DIR__ . '/bin')
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'binary_operator_spaces' => ['align_double_arrow' => true, 'align_equals' => true],
        'single_quote' => false,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'dir_constant' => true,
    ])
    ->setUsingCache(true)
    ->setFinder($finder);
;