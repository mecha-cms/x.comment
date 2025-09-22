<?php

Page::_('comments', function () {
    if ($path = $this->path) {
        $folder = strtr(dirname($path), [LOT . D . 'page' . D => LOT . D . 'comment' . D]) . D . pathinfo($path, PATHINFO_FILENAME);
        $comments = Comments::from($folder, 'page')->not(function ($v) {
            return $v->parent();
        });
        $comments->status = (int) ($this->state['x']['comment'] ?? 1);
        $comments->title = i(0 === ($count = $comments->count) ? '0 Comments' : (1 === $count ? '1 Comment' : '%d Comments'), [$count]);
        return $comments;
    }
    $comments = new Comments;
    $comments->status = (int) ($this->state['x']['comment'] ?? 1);
    $comments->title = i('0 Comments');
    return $comments;
});