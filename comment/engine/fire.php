<?php

function fn_comment_url($s) {
    global $url;
    $path = Path::F(To::path(Path::D($s)), COMMENT);
    $id = (new Date(Path::N($s)))->unix;
    return $url . '/' . To::url($path) . '#' . __replace__(Extend::state('comment', 'anchor')[0], ['id' => $id]);
}

Hook::set('comment.url', 'fn_comment_url');

function fn_comments_path($path, $id) {
    if ($id === 'comments' && !$path) {
        return Path::D(__DIR__) . DS . 'lot' . DS . 'worker' . DS . 'comments.php';
    }
    return $path;
}

Hook::set('shield.get.path', 'fn_comments_path');

function fn_comments_set($path = "", $step = 1) {
    global $site, $language;
    $comments = $files = [];
    if ($folder = Folder::exist(COMMENT . DS . $path)) {
        $files = glob($folder . DS . '*.page');
        foreach ($files as $v) {
            $comments[$v] = new Comment($v);
        }
        asort($comments);
    }
    Lot::set('comments', $comments);
    Hook::set('page.comments', function($v) use($files, $language) {
        $i = count($files);
        return (object) array_merge([
            'i' => $i,
            'x' => false, // disable comment(s)
            'text' => $i . ' ' . $language->{$i === 1 ? 'comment' : 'comments'}
        ], (array) $v);
    });
}

Route::hook(['%*%/%i%', '%*%'], 'fn_comments_set');

// Apply the block filter(s) of `page.content` to the `comment.content`
if (function_exists('fn_block_x')) Hook::set('comment.content', 'fn_block_x', 0);
if (function_exists('fn_block')) Hook::set('comment.content', 'fn_block', 1);

// Apply the Markdown filter of `page.title` to the `comment.title` (if any)
// Apply the Markdown filter of `page.content` to the `comment.content`
if (function_exists('fn_markdown_span')) Hook::set('comment.title', 'fn_markdown_span', 2);
if (function_exists('fn_markdown')) Hook::set(['comment.description', 'comment.content'], 'fn_markdown', 2);

// Apply the user filter(s) of `page.author` to the `comment.author`
if (function_exists('fn_user')) Hook::set('comment.author', 'fn_user', 1);