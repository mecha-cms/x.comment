<?php

return [
    'anchor' => ['comment-%{id}%', 'form-comment'],
    'path' => '-comment',
    'level' => 3, // Set to `1` to disable comment thread, `2` or more to enable comment thread
    'enter' => true, // Show log in link if user extension available?
    'comment' => [
        'state' => 'page', // Default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
        'status' => 2,
        'type' => Config::get('page.type', 'HTML') // Inherit `page.type` state or `HTML`
    ],
    'max' => [
        'author' => 100,
        'email' => 100,
        'link' => 100,
        'content' => 1700
    ],
    'min' => [
        'author' => 1,
        'email' => 1,
        'link' => 0,
        'content' => 1
    ],
    'query_x' => ['<script ', '<iframe '], // Block by word(s)
    'user_ip_x' => [], // Block by IP address(es)
    'user_agent_x' => [] // Block by user agent word(s)
];