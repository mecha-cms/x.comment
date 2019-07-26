<?php

class Comment extends Page {

    public function __construct(string $path = null, array $lot = [], array $prefix = []) {
        $f = Path::R(dirname($path), COMMENT, '/');
        $id = sprintf('%u', (new Date(Path::N($path)))->format('U')); // Comment ID by time
        parent::__construct($path, array_replace_recursive([
            'url' => $GLOBALS['url'] . '/' . $f . '#' . sprintf(state('comment')['anchor'][0], $id)
        ], $lot), $prefix);
    }

    public function comments(int $chunk = 100, int $i = 0): Comments {
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
                    if ($comment['parent'] === $parent) {
                        $comments[] = $comment->path;
                        ++$count; // Count comment(s), filter by `parent` property
                    }
                }
            }
        }
        $comments = new Comments($comments);
        $comments->title = $GLOBALS['language']->commentReplyCount($count);
        return $comments;
    }

    public function parent() {
        return $this->exist ? content(Path::F($this->path) . DS . 'parent.data') : null;
    }

}