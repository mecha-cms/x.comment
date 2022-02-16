<?php namespace x\comment;

function avatar($avatar, array $lot = []) {
    extract($GLOBALS, \EXTR_SKIP);
    $avatar = $avatar ?? $state->x->comment->avatar ?? "";
    if ($avatar) {
        $w = $lot[0] ?? 72;
        $h = $lot[1] ?? $w;
        $avatar = \sprintf($avatar, \md5($this->email), $w, $h);
        if (1 !== $this['status']) {
            return $avatar;
        }
    }
    $user = $this['author'];
    if ($user && \is_string($user) && 0 === \strpos($user, '@') && isset($state->x->user)) {
        if (\is_file($user = \LOT . \DS . 'user' . \DS . \substr($user, 1) . '.page')) {
            return (new \User($user))->avatar(...$lot) ?? $avatar;
        }
    }
    return $avatar;
}

\Hook::set('comment.avatar', __NAMESPACE__ . "\\avatar", 0);

// Extend user property to comment property
if (isset($state->x->user)) {
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
    \Hook::set('comment.email', __NAMESPACE__ . "\\email", 0);
    \Hook::set('comment.link', __NAMESPACE__ . "\\link", 0);
}