<?php namespace fn\comment;

foreach (\g(__DIR__ . DS . '..' . DS . 'lot' . DS . 'worker', 'php') as $v) {
    \Shield::set(\Path::N($v), $v);
}

function comments($source = [], array $lot = []) {
    global $language;
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $parent = \Path::R(\Path::F($path), PAGE);
        foreach (\g(COMMENT . DS . $parent, 'page', "", true) as $v) {
            $comment = new \Comment($v);
            if (!$comment->parent) {
                $comments[] = $comment;
            }
            ++$count;
        }
    } else {
        $source['x'] = true;
    }
    $comments = new \Anemon($comments);
    $comments->x = $source['x'] ?? false; // Disable comment?
    if (isset($source['text'])) {
        $comments->text = \candy($source['text'], [
            'count' => $count,
            'text' => [
                0 => $language->comment,
                1 => $language->comments
            ]
        ]);
    } else {
        $comments->text = $count . ' ' . $language->{'comment' . ($count === 1 ? "" : 's')};
    }
    return $comments;
}

function replys($source = [], array $lot = []) {
    global $language;
    $replys = [];
    if ($path = $this->path) {
        $parent = \Path::N($path);
        foreach (\g(\Path::D($path), 'page', "", true) as $v) {
            $comment = new \Comment($v);
            if ($comment->parent === $parent) {
                $replys[] = $comment;
            }
        }
    } else {
        $source['x'] = true;
    }
    $replys = new \Anemon($replys);
    $count = $replys->count();
    $replys->x = $source['x'] ?? false; // Disable comment?
    if (isset($source['text'])) {
        $replys->text = \candy($source['text'], [
            'count' => $count,
            'text' => [
                0 => $language->comment_reply,
                1 => $language->comment_replys
            ]
        ]);
    } else {
        $replys->text = $count . ' ' . $language->{'comment_reply' . ($count === 1 ? "" : 's')};
    }
    return $replys;
}

\Hook::set('*.comments', __NAMESPACE__ . "\\comments", 0);
\Hook::set('comment.replys', __NAMESPACE__ . "\\replys", 0);

// Extend user property to comment property
if (\Extend::exist('user')) {
    function user($v = "", array $lot = []) {
        if ($v || $this->status(false) !== 1) {
            return $v;
        }
        $user = $this->author(false);
        if ($user && \is_string($user) && \strpos($user, '@') === 0) {
            if ($user = \File::exist(USER . DS . \substr($user, 1) . '.page')) {
                $user = new \User($user);
                $k = \explode('.', $this->_hook, 2)[1] ?? "";
                if ($k === 'link') {
                    // Return `link` property or `url` property or the initial value
                    return $user->get($k, $user->get('url', $v));
                }
                return $user->get($k, $v);
            }
        }
        return $v;
    }
    \Hook::set([
        'comment.avatar',
        'comment.email',
        'comment.link'
    ], __NAMESPACE__ . "\\user", 0);
}

// Build tool(s) from array
function tools(array $in, array $lot = []) {
    $out = [];
    foreach ($in as $v) {
        if (\is_array($v)) {
            $out[] = \HTML::a(...$v);
        } else if (\is_callable($v)) {
            $out[] = \fn($v, $lot, $this, \Shield::class);
        } else {
            $out[] = $v;
        }
    }
    return $out;
}