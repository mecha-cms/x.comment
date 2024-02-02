<?php

class Comments extends Pages {

    public function page(...$lot) {
        return Comment::from(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}