<?php namespace _\lot\x\comment;

// Extend user property to comment property
if (\state('user') !== null) {
    function avatar($avatar, array $lot = []) {
        if (!$avatar) {
            $w = $lot[0] ?? 72;
            $h = $lot[1] ?? $w;
            $d = $lot[2] ?? 'monsterid';
            $avatar = $GLOBALS['url']->protocol . 'www.gravatar.com/avatar/' . \md5($this['email']) . '?s=' . $w . '&d=' . $d;
        }
        if ($avatar || $this['status'] !== 1) {
            return $avatar;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && \strpos($user, '@') === 0) {
            if (\is_file($user = \USER . \DS . \substr($user, 1) . '.page')) {
                return (new \User($user))->avatar ?? $avatar;
            }
        }
        return $avatar;
    }
    function email($email) {
        if ($email || $this['status'] !== 1) {
            return $email;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && \strpos($user, '@') === 0) {
            if (\is_file($user = \USER . \DS . \substr($user, 1) . '.page')) {
                return (new \User($user))->email ?? $email;
            }
        }
        return $email;
    }
    function link($link) {
        if ($link || $this['status'] !== 1) {
            return $link;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && \strpos($user, '@') === 0) {
            if (\is_file($user = \USER . \DS . \substr($user, 1) . '.page')) {
                $user = new \User($user);
                return $user->link ?? $user->url ?? $link;
            }
        }
        return $link;
    }
    \Hook::set('comment.avatar', __NAMESPACE__ . "\\avatar", 0);
    \Hook::set('comment.email', __NAMESPACE__ . "\\email", 0);
    \Hook::set('comment.link', __NAMESPACE__ . "\\link", 0);
}

// Loading asset(s)…
\Hook::set('set', function() {
    if (\Config::is('page')) {
        $path = __DIR__ . \DS . '..' . \DS . '..' . \DS . 'lot' . \DS . 'asset' . \DS;
        \Asset::set($path . 'css' . \DS . 'comment.min.css', 10);
        \Asset::set($path . 'js' . \DS . 'comment.min.js', 10, [
            'src' => function($src) {
                return $src . '#' . \state('comment')['anchor'][1];
            }
        ]);
    }
}, 0);