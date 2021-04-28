<?php

Layout::set('comment', File::exist([
    LOT . DS . 'layout' . DS . 'comment.php',
    __DIR__ . DS . 'layout' . DS . 'comment.php'
]));

Layout::set('comments', File::exist([
    LOT . DS . 'layout' . DS . 'comments.php',
    __DIR__ . DS . 'layout' . DS . 'comments.php'
]));

Layout::set('form/comment', File::exist([
    LOT . DS . 'layout' . DS . 'comment.form.php',
    __DIR__ . DS . 'layout' . DS . 'comment.form.php'
]));
