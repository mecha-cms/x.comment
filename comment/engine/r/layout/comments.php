<?php

$x = P . __FILE__ . P; // Dummy value
$type = $page->get('state.comment') ?? $lot[0] ?? $x;
$count = $page->comments ? $page->comments->count() : 0;

// Comment form is disabled and no comment(s)
if (!$page->comments || (0 === $count && 2 === $type)) {
    $type = 0; // Is the same as disabled comment(s)
}

if (
    // Make sure current page is active
    'page' === $page->x &&
    // Make sure comment feature is active
    ($x === $type || (false !== $type && 0 !== $type))
) {
    if ($parent = Get::get('parent')) {
        // Make sure parent comment exists
        if (is_file($f = LOT . DS . 'comment' . DS . strtr($page->url, [
            $url . '/' => ""
        ]) . DS . $parent . '.page')) {
            $parent = new Comment($f);
        } else {
            // Otherwise, kick!
            Alert::error('Parent comment does not exist.');
            Guard::kick($page->url);
        }
    }
    $lot = [
        'c' => State::get('x.comment', true),
        'count' => $count,
        'parent' => $parent,
        'type' => $x === $type ? 1 : $type
    ];
    if (false === $type) {
        $k = 0;
    } else if (is_numeric($type)) {
        $k = $type;
    } else /* if (true === $type) */ {
        $k = 1;
    }
    echo '<section class="comments comments:' . $k . '">';
    echo x\comment\hook('comments-content', [[
        'header' => self::get(__DIR__ . DS . 'comments.header.php', $lot),
        'body' => self::get(__DIR__ . DS . 'comments.body.php', $lot),
        'footer' => self::get(__DIR__ . DS . 'comments.footer.php', $lot)
    ]], $page);
    echo '</section>';
}