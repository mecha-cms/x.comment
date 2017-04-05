<?php

class Comment extends Page {

    public function __construct($input, $lot = [], $NS = 'comment') {
        $time = is_array($input) ? (array_key_exists('time', $input) ? $input['time'] : date(DATE_WISE)) : (new Date(Path::N($input)))->format(DATE_WISE);
        $date = new Date($time);
        parent::__construct($input, array_merge([
            'time' => $time,
            'date' => $date,
            'id' => (string) $date->unix
        ], $lot), $NS);
        self::$__instance__[] = $this;
    }

}