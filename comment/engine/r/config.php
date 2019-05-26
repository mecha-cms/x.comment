<?php

// Store comment state to registry…
$state = extend('comment');
if (!empty($state['comment'])) {
    // Prioritize default state
    Config::over($state);
    Comment::$data = array_replace_recursive(Page::$data, (array) Config::get('comment', true));
}