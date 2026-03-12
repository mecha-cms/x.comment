<?php

class Comments extends Pages {

    public function page(...$lot) {
        static $c = [];
        if (isset($c[$id = json_encode($lot)])) {
            return $c[$id];
        }
        return ($c[$id] = new Comment(...$lot));
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'comment';
        return parent::from(...$lot);
    }

}