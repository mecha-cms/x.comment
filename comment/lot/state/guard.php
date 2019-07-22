<?php

return [
    'max' => [
        'author' => 100,
        'email' => 100,
        'link' => 100,
        'content' => 1700
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
];