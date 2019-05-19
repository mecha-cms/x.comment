<?php namespace _\comment;

// Extend user property to comment property
if (\extend('user') !== null) {
    function user($v = "", array $lot = []) {
        if ($v || $this['status'] !== 1) {
            return $v;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && \strpos($user, '@') === 0) {
            if ($user = \File::exist(USER . DS . \substr($user, 1) . '.page')) {
                $user = new \User($user);
                $k = \explode('.', $this->_hook, 2)[1] ?? "";
                if ($k === 'link') {
                    // Return `link` property or `url` property or the initial value
                    return $user->get($k) ?? $user->get('url') ?? $v;
                }
                return $user->get($k) ?? $v;
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

// Loading asset(s)…
\Hook::set('set', function() {
    if (\Config::is('page')) {
        $path = __DIR__ . DS . '..' . DS . '..' . DS . 'lot' . DS . 'asset' . DS;
        \Asset::set($path . 'css' . DS . 'comment.min.css', 10);
        \Asset::set($path . 'js' . DS . 'comment.min.js', 10, [
            'src' => function($src) {
                return $src . '#' . \extend('comment')['anchor'][1];
            }
        ]);
    }
}, 0);