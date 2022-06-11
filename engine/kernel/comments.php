<?php

class Comments extends Pages {

    public $page = null;
    public $parent = null;

    public function page(string $path = null, array $lot = []) {
        $comment = new Comment($path, $lot);
        $comment->page = $this->page;
        $comment->parent = $this->parent;
        return $comment;
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return parent::from($v);
        }
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}