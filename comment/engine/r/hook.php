<?php namespace _\lot\x\comment;

// Extend user property to comment property
if (null !== \State::get('x.user')) {
    function avatar($avatar, array $lot = []) {
        if (!$avatar) {
            $w = $lot[0] ?? 72;
            $h = $lot[1] ?? $w;
            $d = $lot[2] ?? 'mp';
            $avatar = $GLOBALS['url']->protocol . 'www.gravatar.com/avatar/' . \md5($this->email) . '.jpg?s=' . $w . '&d=' . $d;
        }
        if ($avatar || 1 !== $this['status']) {
            return $avatar;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && 0 === \strpos($user, '@')) {
            if (\is_file($user = \LOT . \DS . 'user' . \DS . \substr($user, 1) . '.page')) {
                return (new \User($user))->avatar(...$lot) ?? $avatar;
            }
        }
        return $avatar;
    }
    function email($email) {
        if ($email || 1 !== $this['status']) {
            return $email;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && 0 === \strpos($user, '@')) {
            if (\is_file($user = \LOT . \DS . 'user' . \DS . \substr($user, 1) . '.page')) {
                return (new \User($user))->email ?? $email;
            }
        }
        return $email;
    }
    function link($link) {
        if ($link || 1 !== $this['status']) {
            return $link;
        }
        $user = $this['author'];
        if ($user && \is_string($user) && 0 === \strpos($user, '@')) {
            if (\is_file($user = \LOT . \DS . 'user' . \DS . \substr($user, 1) . '.page')) {
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
\Hook::set('content', function() {
    $state = \State::get(null, true);
    if (!empty($state['is']['page']) && !empty($state['has']['page'])) {
        $path = __DIR__ . \DS . '..' . \DS . '..' . \DS . 'lot' . \DS . 'asset' . \DS;
        \Asset::set($path . 'css' . \DS . 'comment.min.css', 10);
        \Asset::set($path . 'js' . \DS . 'comment.min.js', 10, [
            'src' => function($src) use($state) {
                return $src . '#' . ($state['x']['comment']['anchor'][1] ?? "");
            }
        ]);
        \State::set([
            'can' => ['comment' => true],
            'has' => ['comments' => !empty($GLOBALS['page']->comments->count())]
        ]);
    }
}, -1); // Need to set a priority before any asset(s) insertion task(s) because we use the `content` hook