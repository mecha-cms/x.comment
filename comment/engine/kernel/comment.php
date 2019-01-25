<?php

class Comment extends Page {

    const session = 'comment';

    public function __construct(string $path = null, array $lot = [], $NS = []) {
        $f = Path::R(dirname($path), COMMENT, '/');
        $id = sprintf('%u', (new Date(Path::N($path)))->format('U')); // Comment ID by time
        parent::__construct($path, extend([
            'url' => $GLOBALS['URL']['$'] . '/' . $f . '#' . candy(Extend::state('comment', 'anchor')[0], ['id' => $id])
        ], $lot, false), $NS);
    }

}