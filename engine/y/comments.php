<?php

$any = P . uniqid() . P; // Dummy value
$c = State::get('x.comment', true);
$chunk = $c['page']['chunk'] ?? null;
$count = $page->comments ? $page->comments->count() : 0;
$parent = $_GET['parent'] ?? null;
$status = $page->state['x']['comment'] ?? $c['status'] ?? $lot[0] ?? $any;

$path = trim($url->path ?? "", '/');
$route = trim($c['route'] ?? 'comment', '/');

// Calculate last page offset
$max = (int) ceil($count / ($chunk ?? $count));

// Show last page by default if page offset is not available in URL
if (false !== strpos($path . '/', '/' . $route . '/') && preg_match('/\/' . x($route) . '\/([1-9]\d*)$/', $path, $m)) {
    $part = (int) $m[1];
} else {
    $part = $max;
}

// Comment form is disabled and there are no comment(s)
if (!$page->comments || (0 === $count && 2 === $status)) {
    $status = 0; // Is the same as disabled comment(s)
}

if (
    // Make sure current page is active
    'page' === $page->x &&
    // Make sure comment feature is active
    ($any === $status || (false !== $status && 0 !== $status))
) {
    if ($parent) {
        // Make sure parent comment exists
        if (is_file($path = strtr(dirname($page->path), [LOT . D . 'page' . D => LOT . D . 'comment' . D]) . D . $page->name . D . $parent . '.page')) {
            $parent = new Comment($path);
        } else {
            // Otherwise, kick!
            class_exists('Alert') && Alert::error('Parent comment does not exist.');
            kick($page->url);
        }
    }
    if (false === $status) {
        $k = 0;
    } else if (is_numeric($status)) {
        $k = $status;
    } else /* if (true === $status) */ {
        $k = 1;
    }
    $lot = [
        'c' => $c,
        'chunk' => $chunk,
        'count' => $count,
        'k' => $k,
        'max' => $max,
        'page' => $page,
        'parent' => $parent,
        'part' => $part,
        'status' => $any === $status ? 1 : $status
    ];
    echo new HTML(x\comment\y__comments($lot), true);
}