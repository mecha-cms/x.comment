<?php namespace fn\comment;

foreach (\g(__DIR__ . DS . '..' . DS . 'lot' . DS . 'worker', 'php') as $v) {
    \Shield::set(\Path::N($v), $v);
}

function comments_comments($comments = [], array $lot = []) {
    global $language;
    $data = [
        'count' => 0,
        'data' => [],
        'text' => '0 ' . $language->comment_replys,
        'x' => false, // disable comment?
    ];
    if (!$path = $this->path) {
        $data['x'] = true;
    }
    $i = 0;
    $self = \Path::N($path);
    foreach (\g(\Path::D($path), 'page', "", true) as $v) {
        $comment = new \Comment($v);
        if ($comment->parent === $self) {
            $comments['data'][] = $comment;
            ++$i;
        }
    }
    return \extend($data, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment_reply' . ($i === 1 ? "" : 's')}
    ], (array) $comments, false);
}

function comments($comments = [], array $lot = []) {
    global $language, $url;
    $data = [
        'count' => 0,
        'data' => [],
        'text' => '0 ' . $language->comments,
        'x' => false, // disable comment?
    ];
    if (!$path = $this->path) {
        $data['x'] = true;
    } else if (strpos($path, COMMENT . DS) === 0) {
        return (array) $comments; // do not nest this `*.comments` hook to the comment page
    }
    $i = 0;
    if ($folder = \Folder::exist(COMMENT . DS . $url->path(DS))) {
        foreach (\g($folder, 'page', "", true) as $v) {
            $comment = new \Comment($v);
            if (!$comment->parent) {
                $comments['data'][] = $comment;
            }
            ++$i;
        }
    }
    return \extend($data, [
        'count' => $i,
        'text' => $i . ' ' . $language->{'comment' . ($i === 1 ? "" : 's')}
    ], (array) $comments, false);
}

\Hook::set('*.comments', __NAMESPACE__ . '\comments', 0);
\Hook::set('comment.comments', __NAMESPACE__ . '\comments_comments', 0);

// Extend user propert(y|ies) to comment propert(y|ies)
if (\Extend::exist('user')) {
    function user($v = "", array $lot = []) {
        if ($v || $this->status(false) !== 1) {
            return $v;
        }
        $user = $this->author(false);
        if ($user && is_string($user) && strpos($user, '@') === 0) {
            if ($f = \File::exist(USER . DS . substr($user, 1) . '.page')) {
                $f = new \User($f);
                if ($this->_hook === 'comment.link') {
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