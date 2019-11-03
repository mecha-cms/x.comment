<?php

class Comment extends Page {

    public function URL(...$lot) {
        $f = Path::R(dirname($path = $this->path), LOT . DS . 'comment', '/');
        $id = sprintf('%u', parent::time()->format('U')); // Comment ID by time
        return $GLOBALS['url'] . '/' . $f . '#' . sprintf(State::get('x.comment.anchor.0'), $id);
    }

    public function comments(int $chunk = 100, int $i = 0): Comments {
        $comments = [];
        $count = 0;
        if ($path = $this->path) {
            $parent = Path::N($path);
            foreach (g(dirname($path), 'page') as $k => $v) {
                $comment = new static($k);
                if ($parent === $comment['parent']) {
                    $comments[] = $k;
                    ++$count; // Count comment(s), filter by `parent` property
                }
            }
            sort($comments);
        }
        $comments = 0 === $chunk ? [$comments] : array_chunk($comments, $chunk, false);
        $comments = new Comments($comments[$i] ?? []);
        $comments->title = i('%d Repl' . (1 === $count ? 'y' : 'ies'), $count);
        return $comments;
    }

    public function parent(int $i = 1) {
        return $this->exist ? content(Path::F($this->path) . DS . 'parent.data') : null;
    }

}