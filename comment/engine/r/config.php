<?php

// Store comment state to registry…
$state = Extend::state('comment');
if (!empty($state['comment'])) {
    // Prioritize default state
    Config::alt($state);
    Comment::$data = array_replace_recursive(Page::$data, (array) Config::get('comment', true));
}