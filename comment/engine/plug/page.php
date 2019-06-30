<?php

Page::_('comments', function(int $chunk = 100, int $i = 0): Comments {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $r = Path::R(Path::F($path), PAGE);
        $files = [];
        foreach (g(COMMENT . DS . $r, 'page') as $v) {
            ++$count; // Count comment(s), no filter
            if (is_file($rr = Path::F($v) . DS . 'parent.data') && filesize($rr) > 0) {
                // Has parent comment, skip!
                continue;
            } else if (is_file($v)) {
                $parent = false;
                foreach (stream($v) as $ii => $ss) {
                    if ($ii === 0 && $ss !== '---') {
                        break; // No header(s), no parent!
                    }
                    if ($ss === '...') {
                        break; // End header(s), no parent!
                    }
                    if (
                        strpos($ss, 'parent:') === 0 ||
                        strpos($ss, '"parent":') === 0 ||
                        strpos($ss, "'parent':") === 0
                    ) {
                        // Has parent comment!
                        $parent = true;
                        break;
                    }
                }
                if ($parent) {
                    // Has parent comment, skip!
                    continue;
                }
            }
            $files[] = $v;
        }
        sort($files);
        $files = $chunk === 0 ? [$files] : array_chunk($files, $chunk, false);
        $comments = $files[$i] ?? [];
    }
    $comments = new Comments($comments);
    $comments->title = $GLOBALS['language']->commentCount($count);
    return $comments;
});