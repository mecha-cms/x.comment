<?php

return [
    'anchor' => ['comment-%s', 'form-comment', 'comments'],
    'chunk' => 9999, // TODO: Comment pagination
    'deep' => 2, // Set to `0` to disable comment thread, `1` or more to enable comment thread
    'enter' => true, // Show log-in link if user extension available?
    'comment' => [
        'x' => 'page', // Default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
        'status' => 2,
        'type' => Config::get('page.type') ?? 'HTML' // Inherit `page.type` state or `HTML`
    ],
    'max' => [
        'author' => 100,
        'email' => 100,
        'link' => 100,
        'content' => 1700
    ],
    'min' => [
        'author' => 1,
        'email' => 3, // `a@b`
        'link' => 0,
        'content' => 2
    ],
    'x' => [
        'query' => ['</script>', '</iframe>'], // Block by word(s)
        'ip' => [], // Block by IP address(es)
        'ua' => [] // Block by user agent word(s)
    ]
];