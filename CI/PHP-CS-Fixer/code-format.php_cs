<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->exclude([__DIR__ .  '/../../vendor'])
    ->in([__DIR__ .  '/../../']);

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR2' => true,
        'strict_param' => false,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'unary_operator_spaces' => true,
        'function_typehint_space' => true,
        'return_type_declaration' => ['space_before' => 'one'],
        'binary_operator_spaces' => true
    ])
    ->setFinder($finder);
