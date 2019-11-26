<?php

class Comments extends Pages {

    public function page(string $path) {
        return new Comment($path);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . DS . 'comment';
        return parent::from(...$lot);
    }

}