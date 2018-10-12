<?php

class Comment extends Page {

    public function __construct($path = null, array $lot = [], $NS = []) {
        $f = Path::F(dirname($path), COMMENT, '/');
        $id = sprintf('%u', (new Date(Path::N($path)))->unix);
        parent::__construct($path, extend([
            'url' => $GLOBALS['URL']['$'] . '/' . $f . '#' . candy(Extend::state('comment', 'anchor')[0], ['id' => $id])
        ], $lot), $NS);
    }

}