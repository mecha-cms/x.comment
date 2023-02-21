<?php

Page::_('comments', function (int $chunk = 100, int $i = 0) {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $folder = strtr(dirname($path), [LOT . D . 'page' . D => LOT . D . 'comment' . D]) . D . pathinfo($path, PATHINFO_FILENAME);
        foreach (g($folder, 'page') as $k => $v) {
            ++$count; // Count comment(s), no filter
            if (!is_file($file = dirname($k) . D . pathinfo($k, PATHINFO_FILENAME) . D . 'parent.data') || 0 === filesize($file)) {
                $comments[] = $k;
            }
        }
        sort($comments);
    }
    $comments = (new Comments($comments))->chunk($chunk, $i);
    $comments->status = (int) ($this->state['x']['comment'] ?? 1);
    $comments->title = i(0 === $count ? '0 Comments' : (1 === $count ? '1 Comment' : '%d Comments'), [$count]);
    return $comments;
});