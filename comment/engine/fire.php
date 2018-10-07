<?php namespace fn\comment;

foreach (\g(__DIR__ . DS . '..' . DS . 'lot' . DS . 'worker', 'php') as $v) {
    \Shield::set(\Path::N($v), $v);
}

function comments_comments($comments, $lot = [], $that) {
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
    foreach (\g(\Path::D($path), 'page', "", true) as $v) {
        $comment = new \Comment($v);
        if ($comment->get('parent') === \Path::N($path)) {
            $comments['data'][] = $comment;
            ++$i;
        }
    }
    return array_replace($a, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment_reply' . ($i === 1 ? "" : 's')}
    ], $comments);
}

function comments($comments, $lot = [], $that) {
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
    if ($folder = \Folder::exist(COMMENT . DS . $url->path(DS))) {
        foreach (\g($folder, 'page', "", true) as $v) {
            $comment = new \Comment($v);
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

\Hook::set('*.comments', __NAMESPACE__ . '\comments', 0);
\Hook::set('comment.comments', __NAMESPACE__ . '\comments_comments', 0);

// Extend user propert(y|ies) to comment propert(y|ies)
if (\Extend::exist('user')) {
    function user($v, $lot = [], $that, $key) {
        if ($v || $that->get('status', false) !== 1) {
            return $v;
        }
        $user = $that->get('author', false);
        if ($user && is_string($user) && strpos($user, '@') === 0) {
            if ($f = \File::exist(USER . DS . substr($user, 1) . '.page')) {
                $f = new \User($f);
                if ($key === 'link') {
                    // Return `link` property or `url` property or self value
                    return $f->get($key, $f->get('url', $v));
                }
                return $f->get($key, $v);
            }
        }
        return $v;
    }
    \Hook::set([
        'comment.avatar',
        'comment.email',
        'comment.link'
    ], __NAMESPACE__ . '\user', 0);
}