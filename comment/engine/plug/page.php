<?php

Page::_('comments', function(int $chunk = 100, int $i = 0): Comments {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $r = Path::R(Path::F($path), PAGE);
        foreach (g(COMMENT . DS . $r, 'page') as $k => $v) {
            ++$count; // Count comment(s), no filter
            if (is_file($kk = Path::F($k) . DS . 'parent.data') && filesize($kk) > 0) {
                // Has parent comment, skip!
                continue;
            } else {
                $parent = false;
                foreach (stream($k) as $kk => $vv) {
                    if (0 === $kk && '---' !== $vv) {
                        // No header marker means no property at all
                        break;
                    }
                    if ('...' === $vv) {
                        // End header marker means no `parent` property found
                        break;
                    }
                    if (
                        0 === strpos($vv, 'parent:') ||
                        0 === strpos($vv, '"parent":') ||
                        0 === strpos($vv, "'parent':")
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
            $comments[] = $k;
        }
        sort($comments);
    }
    $comments = 0 === $chunk ? [$comments] : array_chunk($comments, $chunk, false);
    $comments = new Comments($comments[$i] ?? []);
    $comments->title = i('%d Comment' . (1 === $count ? "" : 's'), $count);
    return $comments;
});