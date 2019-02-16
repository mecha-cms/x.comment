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

    public function comments(int $chunk = 100, int $i = 0): Anemon {
        $comments = [];
        $count = 0;
        if ($path = $this->path) {
            $parent = Path::N($path);
            $files = g(dirname($path), 'page', "", true);
            $files = array_chunk($files, $chunk, false);
            if (!empty($files[$i])) {
                foreach ($files[$i] as $v) {
                    $comment = new static($v);
                    if ($comment->parent === $parent) {
                        $comments[] = $comment;
                        ++$count; // Count comment(s), filter by `parent` property
                    }
                }
            }
        }
        $comments = new Anemon($comments);
        $comments->title = $count . ' ' . Language::get('comment_reply' . ($count === 1 ? "" : 's'));
        return $comments;
    }

    public function parent() {
        return File::open(Path::F($this->path) . DS . 'parent.data')->get(0);
    }

}