<?php

class Comment extends Page {

    public function __construct($input = null, $lot = [], $NS = ['*', 'comment']) {
        parent::__construct($input, $lot, $NS);
    }

    public static function open($path, $lot = [], $NS = ['*', 'comment']) {
        return parent::open($path, $lot, $NS);
    }

}