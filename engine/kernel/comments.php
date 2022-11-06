<?php

class Comments extends Pages {

    public function page(...$lot) {
        return new Comment(...$lot);
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return parent::from($v);
        }
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}