<?php

return [
    'guard' => [
        // Set this to `true` to manually review new comment(s). This will add a `~` to the start of your comment name
        'defer' => false,
        'max' => [
            'author' => 100,
            'email' => 100,
            'links' => [100],
            'content' => 3000
        ],
        'min' => [
            'author' => 1,
            'email' => 3, // `a@b`
            'links' => [8], // `http://a`
            'content' => 2
        ]
    ],
    'lot' => [
        // Paginate comment(s) if total of comment(s) that has no `parent` property has reached this limit
        'chunk' => 50,
        // Set to `0` to disable comment thread, `1` or more to enable comment thread
        'deep' => 2,
        // Sort comment(s) by the `time` data in ascending order
        'sort' => [1, 'time'],
        // Default comment(s)’ type
        'type' => 'HTML',
        // Default comment(s)’ file extension
        'x' => 'txt'
    ],
    'route' => '/comment',
    // The comment(s)’ visibility (`0` or `false` means “disable comment(s)”, `1` or `true` means “enable comment(s)”, `2` means “close comment(s)”)
    'status' => 1
];