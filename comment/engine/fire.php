<?php

function fn_comment_url($s, $lot) {
    global $url;
    $path = Path::F(Path::D($lot['path']), COMMENT);
    $id = (new Date(Path::N($s)))->unix;
    return $url . '/' . To::url($path) . '#' . __replace__(Extend::state('comment', 'anchor')[0], ['id' => $id]);
}

Hook::set('comment.url', 'fn_comment_url');

function fn_comments_path($path, $id) {
    if (is_string($id) && $id === 'comments' && !$path) {
        return Path::D(__DIR__) . DS . 'lot' . DS . 'worker' . DS . 'comments.php';
    }
    return $path;
}

Hook::set('shield.get.path', 'fn_comments_path');

function fn_comments_set($path = "", $step = 1) {
    global $site, $language;
    $comments = $files = [];
    if ($folder = Folder::exist(COMMENT . DS . $path)) {
        foreach (g($folder, 'page') as $v) {
            $comments[$v] = new Comment($v);
        }
        asort($comments);
    }
    Lot::set('comments', $comments);
}

Route::lot(['%*%/%i%', '%*%'], 'fn_comments_set');

function fn_page_comments($comments, $lot) {
    global $language;
    $comments = (array) $comments;
    $a = [
        'i' => 0,
        'x' => false, // disable comment?
        'text' => '0 ' . $language->comments
    ];
    if (!isset($lot['path'])) {
        return array_replace($a, [
            'x' => true,
            'text' => null
        ]);
    }
    if ($files = g(str_replace(PAGE, COMMENT, Path::F($lot['path'])), 'page')) {
        $i = count($files);
        return (object) array_replace($a, [
            'i' => $i,
            'text' => $i . ' ' . $language->{$i === 1 ? 'comment' : 'comments'}
        ], $comments);
    }
    return (object) array_replace($a, $comments);
}

Hook::set('page.comments', 'fn_page_comments');