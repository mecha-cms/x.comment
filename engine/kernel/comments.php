<?php

class Comments extends Pages {

    public function page(...$lot) {
        if (($v = reset($lot)) && $v instanceof Comment) {
            return $v;
        }
        if (is_array($v) && isset($v["\0"])) {
            return $v["\0"];
        }
        return new Comment(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}