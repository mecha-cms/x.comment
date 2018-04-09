<?php

class Comment extends Page {

    public function __construct($input = [], $lot = [], $NS = []) {
        $path = is_array($input) ? (isset($input['path']) ? $input['path'] : null) : $input;
        $f = Path::F(Path::D($path), COMMENT, '/');
        $id = (new Date(Path::N($path)))->unix;
        parent::__construct($input, array_replace([
            'url' => $GLOBALS['URL']['$'] . '/' . $f . '#' . __replace__(Extend::state('comment', 'anchor')[0], ['id' => $id])
        ], $lot), $NS);
    }

}