<?php

// Create a `comment` folder in `lot` if it is not there
$f = LOT . DS . 'comment';
if (!Folder::exist($f)) {
    Folder::set($f, 0600);
    File::write('deny from all')->saveTo($f . DS . '.htaccess', 0600);
    Guardian::kick($url->current);
}

define('COMMENT_FORM_ID', 'form-comment');

Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'comment.min.css');
Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'js' . DS . 'comment.min.js');

require __DIR__ . DS . 'engine' . DS . 'fire.php';