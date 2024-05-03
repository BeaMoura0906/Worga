<?php

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/src');

return new Doctum($finder, [
    'title'                => 'Documentation de Worga',
    'build_dir'            => __DIR__.'/docs/phpdoc/',
    'cache_dir'            => __DIR__.'/cache',
    'default_opened_level' => 2,
    'versions'             => 'v1.0',
    'language'             => 'fr'
]);