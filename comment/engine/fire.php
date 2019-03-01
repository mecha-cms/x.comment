<?php namespace fn\comment;

foreach (\g(__DIR__ . DS . '..' . DS . 'lot' . DS . 'worker', 'php') as $v) {
    \Shield::set(\Path::N($v), $v);
}

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
            $a = new \HTML;
            $a[0] = $v[0] ?? 'a';
            $a[1] = $v[1] ?? "";
            $a[2] = $v[2] ?? [];
            $out[] = $a;
        } else if (\is_callable($v)) {
            $out[] = \fn($v, $lot, $this, \Shield::class);
        } else {
            $out[] = $v;
        }
    }
    return $out;
}