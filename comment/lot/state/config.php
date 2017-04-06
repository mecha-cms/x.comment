<?php

return [
    'path' => '-comment',
    'x' => 'page', // default file extension for new comment (`draft` to save/moderate the comment and `page` to publish the comment immediately)
    'thread' => 1, // set to `0` or `false` to disable comment thread (TODO: multi-level comment thread)
    'page' => [
        'type' => 'HTML',
        'status' => 2
    ]
];