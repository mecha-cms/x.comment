<?php if ($chunk && $count > $chunk): ?>
  <nav class="comments-pager">
    <?php

    echo (static function($current, $count, $chunk, $peek, $fn, $first, $prev, $next, $last) {
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
            $out .= '<span>';
            if ($current === $begin) {
                $out .= '<a aria-disabled="true" title="' . i('Go to the %s comments', [l($prev)]) . '">' . $prev . '</a>';
            } else {
                $out .= '<a href="' . $fn($current - 1) . '" title="' . i('Go to the %s comments', [l($prev)]) . '" rel="prev">' . $prev . '</a>';
            }
            $out .= '</span> ';
        }
        if ($first && $last) {
            $out .= '<span>';
            if ($min > $begin) {
                $out .= '<a href="' . $fn($begin) . '" title="' . i('Go to the %s comment', [l($first)]) . '" rel="prev">' . $begin . '</a>';
                if ($min > $begin + 1) {
                    $out .= ' <span aria-hidden="true">&#x2026;</span>';
                }
            }
            for ($i = $min; $i <= $max; ++$i) {
                if ($current === $i) {
                    $out .= ' <a aria-current="page" title="' . i('Go to comments %d (you are here)', [$i]) . '">' . $i . '</a>';
                } else {
                    $out .= ' <a href="' . $fn($i) . '" title="' . i('Go to comments %d', [$i]) . '" rel="' . ($current >= $i ? 'prev' : 'next') . '">' . $i . '</a>';
                }
            }
            if ($max < $end) {
                if ($max < $end - 1) {
                    $out .= ' <span aria-hidden="true">&#x2026;</span>';
                }
                $out .= ' <a href="' . $fn($end) . '" title="' . i('Go to the %s comments', [l($last)]) . '" rel="next">' . $end . '</a>';
            }
            $out .= '</span>';
        }
        if ($next) {
            $out .= ' <span>';
            if ($current === $end) {
                $out .= '<a aria-disabled="true" title="' . i('Go to the %s comments', [l($next)]) . '">' . $next . '</a>';
            } else {
                $out .= '<a href="' . $fn($current + 1) . '" title="' . i('Go to the %s comments', [l($next)]) . '" rel="next">' . $next . '</a>';
            }
            $out .= '</span>';
        }
        return $out;
    })($current, $count, $chunk, 2, static function($i) use($c, $max, $page, $route, $url) {
        return $page->url . ($max === $i ? "" : '/' . $route . '/' . $i) . $url->query([
            'parent' => null
        ]) . '#comments';
    }, i('First'), i('Previous'), i('Next'), i('Last')); ?>
  </nav>
  <?php elseif ($part > 1): ?>
  <p role="status">
    <?= i('No more %s to load.', ['comments']); ?>
  </p>
<?php endif; ?>