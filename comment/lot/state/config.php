<?php

return [
    'anchor' => ['comment-%s', 'form-comment', 'comments'],
    'chunk' => 9999, // TODO: Comment pagination
    'deep' => 2, // Set to `0` to disable comment thread, `1` or more to enable comment thread
    'enter' => true, // Show log-in link if user extension available?
    'comment' => [
        'x' => 'page' // Default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
    ]
];