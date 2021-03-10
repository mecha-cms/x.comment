<?php

Hook::set('content', function() {
    $state = State::get(null, true);
    if (!empty($state['is']['page']) && !empty($state['has']['page'])) {
        $path = __DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS;
        $z = defined('DEBUG') && DEBUG ? '.' : '.min.';
        Asset::set($path . 'css' . \DS . 'index' . $z . 'css', 10);
        Asset::set($path . 'js' . \DS . 'index' . $z . 'js', 10);
        State::set([
            'can' => ['comment' => true],
            'has' => ['comments' => !empty($GLOBALS['page']->comments->count())]
        ]);
    }
}, -1); // Need to set a priority before any asset(s) insertion task(s) because we use the `content` hook
