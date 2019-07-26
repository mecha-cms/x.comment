<?php

Page::_('comments', function(int $chunk = 100, int $i = 0): Comments {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $r = Path::R(Path::F($path), PAGE);
        $files = [];
        foreach (g(COMMENT . DS . $r, 'page') as $v) {
            ++$count; // Count comment(s), no filter
            if (is_file($vv = Path::F($v) . DS . 'parent.data') && filesize($vv) > 0) {
                // Has parent comment, skip!
                continue;
            } else if (is_file($v)) {
                $parent = false;
                foreach (stream($v) as $kk => $vv) {
                    if ($kk === 0 && $vv !== '---') {
                        // No header marker means no property at all
                        break;
                    }
                    if ($vv === '...') {
                        // End header marker means no `parent` property found
                        break;
                    }
                    if (
                        strpos($vv, 'parent:') === 0 ||
                        strpos($vv, '"parent":') === 0 ||
                        strpos($vv, "'parent':") === 0
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