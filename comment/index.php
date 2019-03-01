<?php

require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'config.php';
require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'route.php';

// Require the plug manually…
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'comment.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'get.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'page.php';

Hook::set('enter', function() {
    if (Config::is('page')) {
        $path = __DIR__ . DS . 'lot' . DS . 'asset' . DS;
        Asset::set($path . 'css' . DS . 'comment.min.css', 10);
        Asset::set($path . 'js' . DS . 'comment.min.js', 10, [
            'src' => function($src) {
                return $src . '#' . Extend::state('comment', 'anchor')[1];
            }
        ]);
    }
}, 0);

require __DIR__ . DS . 'engine' . DS . 'fire.php';