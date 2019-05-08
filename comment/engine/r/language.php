<?php

Language::set([
    'comment' => ['Comment', 'Comment', 'Comments'],
    'comment-author' => 'Name',
    'comment-content' => 'Message',
    'comment-count' => function(int $i) {
        return $i . ' Comment' . ($i === 1 ? "" : 's');
    },
    'comment-link' => 'URL',
    'comment-mail' => 'E-Mail',
    'comment-placeholder-as' => 'Comment as %s',
    'comment-placeholder-author' => 'Anonymous',
    // There are `&zwnj;` character(s) added just before and after `://`
    // to prevent minify extension minifying this placeholder value
    'comment-placeholder-link' => 'http‌://‌',
    // There are also `&zwnj;` character(s) added just before and after `@`
    'comment-placeholder-mail' => 'hello‌@‌' . $GLOBALS['URL']['host'],
    'comment-placeholder-content' => 'Message goes here…',
    'comment-placeholder-reply' => 'Reply to %s',
    'comment-reply-count' => function(int $i) {
        return $i . ' Repl' . ($i === 1 ? 'y' : 'ies');
    },
    'message-error-comment-exist' => 'You have sent that comment already.',
    'message-error-comment-i-p' => 'Blocked IP address: <em>%s</em>.',
    'message-error-comment-max' => '%s too long.',
    'message-error-comment-min' => '%s too short.',
    'message-error-comment-pattern-field' => 'Invalid %s format.',
    'message-error-comment-query' => 'Please choose another word: <em>%s</em>.',
    'message-error-comment-source' => 'You cannot write a comment here. This is usually due to the page data that is dynamically generated.',
    'message-error-comment-token' => 'Invalid token.',
    'message-error-comment-u-a' => 'Blocked user agent: <em>%s</em>.',
    'message-error-comment-void-field' => 'Please fill out the %s field.',
    'message-info-comment-save' => 'Your comment will be visible once approved by the author.',
    'message-info-comment-x' => 'Comments are closed.',
    'message-success-comment-create' => 'Comment created.',
    'o:page-state' => [
        'comment' => ['Disable comments?', 2]
    ]
]);