<?php

// Create a `comment` folder in `lot` if it is not there
$f = LOT . DS . 'comment';
if (!Folder::exist($f)) {
    Folder::set($f, 0755);
    File::write('deny from all')->saveTo($f . DS . '.htaccess', 0600);
    Guardian::kick($url->current);
}

Hook::set('asset.bottom', function($content) {
    $a = [
        'id' => Extend::state(__DIR__, 'anchor')[1]
    ];
    return $content . '<script>window.COMMENT=' . json_encode($a) . ';</script>';
}, 9.9);

Hook::set('shield.before', function() use($site) {
    if ($site->is === 'page') {
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'comment.min.css', 10);
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'js' . DS . 'comment.min.js', 10);
    }
});

require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'route.php';
require __DIR__ . DS . 'engine' . DS . 'fire.php';