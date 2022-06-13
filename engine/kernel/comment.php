<?php

class Comment extends Page {

    public $page = null;
    public $parent = null;

    public function page(array $lot = []) {
        if (!$this->exist()) {
            return null;
        }
        if (!$page = $this->page) {
            $folder = dirname($this->path);
            if ($path = exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                return ($this->page = new Page($path, $lot));
            }
            return null;
        }
        if (!$lot) {
            return $page;
        }
        foreach ($lot as $k => $v) {
            $page->{$k} = $v;
        }
        return $page;
    }

    public function parent(array $lot = []) {
        if (!$this->exist()) {
            return null;
        }
        if (!$parent = $this->parent) {
            $folder = dirname($this->path);
            if (!is_file($file = $folder . D . pathinfo($this->path, PATHINFO_FILENAME) . D . 'parent.data')) {
                return null;
            }
            if (0 === filesize($file)) {
                return null;
            }
            $name = trim((string) fgets(fopen($file, 'r'))); // <https://stackoverflow.com/a/4521969/1163000>
            if ($path = "" !== $name ? exist([
                $folder . D . $name . '.archive',
                $folder . D . $name . '.page'
            ], 1) : false) {
                return ($this->parent = new static($path, $lot));
            }
            return null;
        }
        if (!$lot) {
            return $parent;
        }
        foreach ($lot as $k => $v) {
            $parent->{$k} = $v;
        }
        return $parent;
    }

    public function URL(...$lot) {
        return parent::URL();
        // return $GLOBALS['url'] . '/' . Path::R(dirname($this->path), LOT . DS . 'comment', '/');
    }

    public function comments(int $chunk = 100, int $i = 0) {
        $comments = [];
        $count = 0;
        if ($path = $this->path) {
            foreach (g(dirname($path), 'page') as $k => $v) {
                $comment = new static($k);
                $parent = $comment->parent();
                if ($parent && $path === $parent->path) {
                    $comments[] = $k;
                    ++$count; // Count comment(s), filter by `parent` property
                }
                unset($comment);
            }
            sort($comments);
        }
        $comments = (new Comments($comments))->chunk($chunk, $i);
        $comments->title = i(0 === $count ? '0 Replies' : (1 === $count ? '1 Reply' : '%d Replies'), [$count]);
        return $comments;
    }

}