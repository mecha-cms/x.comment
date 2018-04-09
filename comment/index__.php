<?php

// Create a `comment` folder in `lot` if it is not there
$f = LOT . DS . 'comment';
if (!Folder::exist($f)) {
    Folder::set($f, 0755);
    Guardian::kick($url->current);
}

Hook::set('asset:body', function($content) use($site) {
    if ($site->is('page')) {
        $o = array_replace([
            'id' => Extend::state('comment', 'anchor')[1]
        ], (array) a(Config::get('page.o.js.COMMENT', [])));
        return $content . '<script>window.COMMENT=' . json_encode($o) . ';</script>';
    }
    return $content;
}, 9.9);

Hook::set('shield.enter', function() use($site) {
    if ($site->is('page')) {
        $s = __DIR__ . DS . 'lot' . DS . 'asset' . DS;
        Asset::set($s . 'css' . DS . 'comment.min.css', 10);
        Asset::set($s . 'js' . DS . 'comment.min.js', 10);
    }
}, 0);

require __DIR__ . DS . 'lot' . DS . 'worker' . DS . 'worker' . DS . 'route.php';
require __DIR__ . DS . 'engine' . DS . 'fire.php';