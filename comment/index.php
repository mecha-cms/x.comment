<?php

Hook::set('shield.enter', function() use($site) {
    if ($site->is('page')) {
        $s = __DIR__ . DS . 'lot' . DS . 'asset' . DS;
        Asset::set($s . 'css' . DS . 'comment.min.css', 10);
        Asset::set($s . 'js' . DS . 'comment.min.js', 10, [
            'src' => function($src) {
                return $src . '#' . Extend::state('comment', 'anchor')[1];
            }
        ]);
    }
}, 0);

r(__DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker', [
    'config.php',
    'route.php'
], null, Lot::get(null, []));

require __DIR__ . DS . 'engine' . DS . 'fire.php';