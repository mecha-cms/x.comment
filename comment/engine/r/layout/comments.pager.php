<?php

$i = $url['i'] ?? 1;
$path = $c['path'] ?? '/comment';
$chunk = $c['page']['chunk'] ?? 9999;

if ($page->comments->count() > $chunk): ?>
<nav class="comments-pager">
<?php

if ($i > 1) {
    $current = $path === substr($url['path'], -strlen($path)) ? $i : -1;
} else {
    $current = $i;
}

echo (function($current, $count, $chunk, $peek, $fn, $first, $prev, $next, $last) {
    $begin = 1;
    $end = (int) ceil($count / $chunk);
    $out = "";
    if ($end <= 1) {
        return $out;
    }
    if ($current <= $peek + $peek) {
        $min = $begin;
        $max = min($begin + $peek + $peek, $end);
    } else if ($current > $end - $peek - $peek) {
        $min = $end - $peek - $peek;
        $max = $end;
    } else {
        $min = $current - $peek;
        $max = $current + $peek;
    }
    if ($prev) {
        $out = '<span>';
        if ($current === $begin) {
            $out .= '<b title="' . $prev . '">' . $prev . '</b>';
        } else {
            $out .= '<a href="' . $fn($current - 1) . '" title="' . $prev . '" rel="prev">' . $prev . '</a>';
        }
        $out .= '</span> ';
    }
    if ($first && $last) {
        $out .= '<span>';
        if ($min > $begin) {
            $out .= '<a href="' . $fn($begin) . '" title="' . $first . '" rel="prev">' . $begin . '</a>';
            if ($min > $begin + 1) {
                $out .= ' <span>&#x2026;</span>';
            }
        }
        for ($i = $min; $i <= $max; ++$i) {
            if ($current === $i) {
                $out .= ' <b title="' . $i . '">' . $i . '</b>';
            } else {
                $out .= ' <a href="' . $fn($i) . '" title="' . $i . '" rel="' . ($current >= $i ? 'prev' : 'next') . '">' . $i . '</a>';
            }
        }
        if ($max < $end) {
            if ($max < $end - 1) {
                $out .= ' <span>&#x2026;</span>';
            }
            $out .= ' <a href="' . $fn($end) . '" title="' . $last . '" rel="next">' . $end . '</a>';
        }
        $out .= '</span>';
    }
    if ($next) {
        $out .= ' <span>';
        if ($current === $end) {
            $out .= '<b title="' . $next . '">' . $next . '</b>';
        } else {
            $out .= '<a href="' . $fn($current + 1) . '" title="' . $next . '" rel="next">' . $next . '</a>';
        }
        $out .= '</span>';
    }
    return $out;
})($current, count($page->comments->lot), $chunk, 2, function($i) use($c, $page, $path, $url) {
    return $page->url . ($i > 1 ? $path . '/' . $i : "") . $url->query . '#' . $c['anchor'][2];
}, i('First'), i('Previous'), i('Next'), i('Last')); ?>
</nav>
<?php endif; ?>
