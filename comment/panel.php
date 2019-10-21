<?php

$GLOBALS['_']['lot']['bar']['lot'][0]['lot']['folder']['lot']['comment']['icon'] = 'M17,12V3A1,1 0 0,0 16,2H3A1,1 0 0,0 2,3V17L6,13H16A1,1 0 0,0 17,12M21,6H19V15H6V17A1,1 0 0,0 7,18H18L22,22V7A1,1 0 0,0 21,6Z';

Hook::set('on.comment.set', function($comment) {
    extract($GLOBALS, EXTR_SKIP);
    $id = uniqid();
    file_put_contents(LOT . DS . '.alert' . DS . $id . '.page', To::page([
        'title' => i('New %s', 'Comment'),
        'description' => i('A new %s has been added.', 'comment'),
        'type' => 'Info',
        'link' => $url . $_['/'] . '::g::' . strtr($comment->path, [
            LOT => "",
            DS => '/'
        ])
    ]));
});