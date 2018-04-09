<?php

function fn_comments_path($path, $id) {
    if (is_string($id) && $id === 'comments') {
        $path[] = Path::D(__DIR__) . DS . 'lot' . DS . 'worker' . DS . 'comments.php';
    }
    return $path;
}

Hook::set('shield.path', 'fn_comments_path');

function fn_comment_comments($comments, $lot = [], $that) {
    global $language;
    $comments = $comments ?: [];
    $a = [
        'count' => 0,
        'text' => '0 ' . $language->comment_replys,
        'x' => false, // disable comment?
    ];
    if (!$path = $that->get('path')) {
        $a['x'] = true;
    }
    $i = 0;
    foreach (g(Path::D($path), 'page', "", true) as $v) {
        $comment = new Comment($v);
        if ($comment->get('parent') === Path::N($path)) {
            $comments[] = $comment;
            ++$i;
        }
    }
    return array_replace($a, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment_reply' . ($i === 1 ? "" : 's')}
    ], $comments);
}

function fn_comments($comments, $lot = [], $that) {
    global $language;
    $comments = $comments ?: [];
    $a = [
        'count' => 0,
        'text' => '0 ' . $language->comments,
        'x' => false, // disable comment?
    ];
    if (!$path = $that->get('path')) {
        $a['x'] = true;
    }
    $i = 0;
    if ($folder = Folder::exist(COMMENT . DS . Path::F($path, PAGE))) {
        foreach (g($folder, 'page', "", true) as $v) {
            $comment = new Comment($v);
            if (!$comment->get('parent')) {
                $comments[] = $comment;
                ++$i;
            }
        }
    }
    return array_replace($a, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment' . ($i === 1 ? "" : 's')}
    ], $comments);
}

Hook::set('*.comments', 'fn_comments', 0);
Hook::set('comment.comments', 'fn_comment_comments', 0);