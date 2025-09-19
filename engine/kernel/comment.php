<?php

class Comment extends Page {

    public function page(array $lot = []) {
        if (!$this->_exist()) {
            return null;
        }
        $path = $this['page'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            $folder = strtr(dirname($this->path), [LOT . D . 'comment' . D => LOT . D . 'page' . D]);
            if (!$path = exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                return null;
            }
        }
        return new Page($path, $lot);
    }

    public function parent(array $lot = []) {
        if (!$this->_exist()) {
            return null;
        }
        $path = $this['parent'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            if (!is_file($path = dirname($this->path) . D . $path . '.page')) {
                return null;
            }
        }
        return new static($path, $lot);
    }

    public function URL(...$lot) {
        if ($page = $this->page()) {
            return $page->url . '#comment:' . $this->id;
        }
        return parent::URL(...$lot);
    }

    public function comments() {
        if ($path = $this->path) {
            $comments = Comments::from(dirname($path), 'page')->is(function ($v) use ($path) {
                return ($parent = $v->parent()) && $path === $parent->path;
            });
            $comments->title = i(0 === ($count = $comments->count) ? '0 Replies' : (1 === $count ? '1 Reply' : '%d Replies'), [$count]);
            return $comments;
        }
        $comments = new Comments;
        $comments->title = i('0 Replies');
        return $comments;
    }

}