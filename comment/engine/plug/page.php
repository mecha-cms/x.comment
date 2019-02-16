<?php

Page::_('comments', function(int $chunk = 100, int $i = 0): Anemon {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $r = Path::R(Path::F($path), PAGE);
        $files = g(COMMENT . DS . $r, 'page', "", true);
        $files = array_chunk($files, $chunk, false);
        if (!empty($files[$i])) {
            foreach ($files[$i] as $v) {
                $comment = new Comment($v);
                $comment->parent || ($comments[] = $comment);
                ++$count; // Count comment(s), no filter
            }
        }
    }
    $comments = new Anemon($comments);
    $comments->title = $count . ' ' . Language::get('comment' . ($count === 1 ? "" : 's'));
    return $comments;
});