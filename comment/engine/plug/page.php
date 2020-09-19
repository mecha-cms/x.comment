<?php

Page::_('comments', function(int $chunk = 100, int $i = 0): Comments {
    $comments = [];
    $count = 0;
    if ($path = $this->path) {
        $r = Path::R(Path::F($path), LOT . DS . 'page');
        foreach (g(LOT . DS . 'comment' . DS . $r, 'page') as $k => $v) {
            ++$count; // Count comment(s), no filter
            if (is_file($kk = Path::F($k) . DS . 'parent.data') && filesize($kk) > 0) {
                // Has parent comment, skip!
                continue;
            } else {
                $parent = false;
                $start = defined("YAML\\SOH") ? YAML\SOH : '---';
                $end = defined("YAML\\EOT") ? YAML\EOT : '...';
                foreach (stream($k) as $kk => $vv) {
                    if (0 === $kk && $start . "\n" !== $vv) {
                        // No header marker means no property at all
                        break;
                    }
                    if ($end . "\n" === $vv) {
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
    $comments = (new Comments($comments))->chunk($chunk, $i);
    $comments->title = i(0 === $count ? '0 Comments' : (1 === $count ? '1 Comment' : '%d Comments'), $count);
    return $comments;
});
