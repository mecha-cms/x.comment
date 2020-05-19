<?php

return [
    'path' => '/comment',
    'page' => [
        // Paginate comment(s) if total of comment(s) that has no `parent` property has reached this limit
        'chunk' => 50,
        // Set to `0` to disable comment thread, `1` or more to enable comment thread
        'deep' => 2,
        // Default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
        'x' => 'page'
    ],
    'anchor' => ['comment:%s', 'form:comment', 'comments'],
    'user' => true, // Show log-in button if user extension is available
    'guard' => [
        'max' => [
            'author' => 100,
            'email' => 100,
            'link' => 100,
            'content' => 3000
        ],
        'min' => [
            'author' => 1,
            'email' => 3, // `a@b`
            'link' => 8, // `http://a`
            'content' => 2
        ],
        'x' => [
            'query' => ['f*ck'], // Block by word(s)
            'ip' => [], // Block by IP address(es)
            'ua' => [] // Block by user agent word(s)
        ]
    ]
];
