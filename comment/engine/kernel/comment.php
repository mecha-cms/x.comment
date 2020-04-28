<?php

class Comment extends Page {

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->h[] = $c;
    }

    public function URL(...$lot) {
        return $GLOBALS['url'] . '/' . Path::R(dirname($this->path), LOT . DS . 'comment', '/');
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
        $comments = (new Comments($comments))->chunk($chunk, $i);
        $comments->title = i(0 === $count ? '0 Replies' : (1 === $count ? '1 Reply' : '%d Replies'), $count);
        return $comments;
    }

    public function parent() {
        return $this->exist ? content(Path::F($this->path) . DS . 'parent.data') : null;
    }

}
