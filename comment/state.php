<?php

return [
    'page' => [
        // Default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
        'x' => 'page'
    ],
    'anchor' => ['comment:%s', 'form-comment', 'comments'],
    'chunk' => 9999, // TODO: Comment pagination
    'deep' => 2, // Set to `0` to disable comment thread, `1` or more to enable comment thread
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
            'query' => ['</script>', '</iframe>'], // Block by word(s)
            'ip' => [], // Block by IP address(es)
            'ua' => [] // Block by user agent word(s)
        ]
    ]
];
