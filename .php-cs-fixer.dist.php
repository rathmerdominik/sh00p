<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
    ;
