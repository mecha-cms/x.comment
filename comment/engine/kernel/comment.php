<?php

class Comment extends Page {

    // Set pre-defined comment property
    public static $data = [];

    public function __construct(string $path = null, array $lot = [], array $prefix = []) {
        $f = Path::R(dirname($path), COMMENT, '/');
        $id = sprintf('%u', (new Date(Path::N($path)))->format('U')); // Comment ID by time
        parent::__construct($path, array_replace_recursive([
            'url' => $GLOBALS['URL']['$'] . '/' . $f . '#' . candy(Extend::state('comment', 'anchor')[0], ['id' => $id])
        ], static::$data, $lot), $prefix);
    }

    public function comments(int $chunk = 100, int $i = 0): Anemon {
        $comments = [];
        $count = 0;
        if ($path = $this->path) {
            $parent = Path::N($path);
            $files = [];
            foreach (g(dirname($path), 'page') as $v) {
                $files[] = $v;
            }
            sort($files);
            $files = $chunk === 0 ? [$files] : array_chunk($files, $chunk, false);
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