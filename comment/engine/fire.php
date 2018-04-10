<?php

foreach (g(__DIR__ . DS . '..' . DS . 'lot' . DS . 'worker', 'php') as $v) {
    Shield::set(Path::N($v), $v);
}

function fn_comment_comments($comments, $lot = [], $that) {
    global $language;
    $comments = $comments ?: [];
    $a = [
        'count' => 0,
        'data' => [],
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
            $comments['data'][] = $comment;
            ++$i;
        }
    }
    return array_replace($a, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment_reply' . ($i === 1 ? "" : 's')}
    ], $comments);
}

function fn_comments($comments, $lot = [], $that) {
    global $language, $url;
    $comments = $comments ?: [];
    $a = [
        'count' => 0,
        'data' => [],
        'text' => '0 ' . $language->comments,
        'x' => false, // disable comment?
    ];
    if (!$path = $that->get('path')) {
        $a['x'] = true;
    } else if (strpos($path, COMMENT . DS) === 0) {
        return $comments; // do not nest this `*.comments` hook to the comment page
    }
    $i = 0;
    if ($folder = Folder::exist(COMMENT . DS . $url->path(DS))) {
        foreach (g($folder, 'page', "", true) as $v) {
            $comment = new Comment($v);
            if (!$comment->get('parent')) {
                $comments['data'][] = $comment;
            }
            ++$i;
        }
    }
    return array_replace($a, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment' . ($i === 1 ? "" : 's')}
    ], $comments);
}

Hook::set('*.comments', 'fn_comments', 0);
Hook::set('comment.comments', 'fn_comment_comments', 0);