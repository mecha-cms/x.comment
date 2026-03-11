<?php

class Comments extends Pages {

    public function page(...$lot) {
        if (($v = $lot[0] ?? 0) instanceof Comment) {
            return $v;
        }
        if (is_array($v)) {
            unset($v[P]);
            $lot[0] = $v;
        }
        return new Comment(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}