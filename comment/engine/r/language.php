<?php

Language::set([
    'alert-error-comment-exist' => 'You have sent that comment already.',
    'alert-error-comment-for' => 'You cannot write a comment here. This is usually due to the page data that is dynamically generated.',
    'alert-error-comment-ip' => 'Blocked IP address: <em>%s</em>.',
    'alert-error-comment-max' => '%s too long.',
    'alert-error-comment-min' => '%s too short.',
    'alert-error-comment-pattern-field' => 'Invalid %s format.',
    'alert-error-comment-query' => 'Please choose another word: <em>%s</em>.',
    'alert-error-comment-token' => 'Invalid token.',
    'alert-error-comment-ua' => 'Blocked user agent: <em>%s</em>.',
    'alert-error-comment-void-field' => 'Please fill out the %s field.',
    'alert-info-comment-save' => 'Your comment will be visible once approved by the author.',
    'alert-info-comment-x' => 'Comments are closed.',
    'alert-success-comment-create' => 'Comment created.',
    'comment' => ['Comment', 'Comment', 'Comments'],
    'comment-alt-as' => 'Comment as %s',
    'comment-alt-author' => 'Anonymous',
    'comment-alt-email' => S . 'hello' . S . '@' . S . $url->host . S,
    // There are also `&zwnj;` character(s) added just before and after `://`
    // to prevent minify extension minifying this placeholder value
    'comment-alt-link' => S . 'http' . S . '://' . S,
    'comment-alt-content' => 'Message goes here…',
    'comment-alt-reply' => 'Reply to %s',
    'comment-author' => 'Name',
    'comment-content' => 'Message',
    'comment-count' => function(int $i) {
        return $i . ' Comment' . ($i === 1 ? "" : 's');
    },
    'comment-link' => 'URL',
    'comment-email' => 'E-Mail',
    'comment-reply-count' => function(int $i) {
        return $i . ' Repl' . ($i === 1 ? 'y' : 'ies');
    }
]);