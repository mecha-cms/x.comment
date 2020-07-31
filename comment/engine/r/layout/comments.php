<?php

$x = P . __FILE__ . P;
$type = $page->get('state.comment') ?? $lot[0] ?? $x;

// Comment form is disabled and no comment(s)
if (!$page->comments || (0 === $page->comments->count() && 2 === $type)) {
    $type = 0; // Is the same as disabled comment(s)
}

if (
    // Make sure current page is active
    'page' === $page->x &&
    // Make sure comment feature is active
    ($x === $type || (false !== $type && 0 !== $type))
):

if ($reply = Get::get('parent')) {
    // Make sure parent comment exists
    if (is_file($f = LOT . DS . 'comment' . $url->path(DS) . DS . $reply . '.page')) {
        $reply = new Comment($f);
    } else {
        // Otherwise, kick!
        Alert::error('Parent comment does not exist.');
        Guard::kick($page->url);
    }
}

$c = [
    'c' => State::get('x.comment', true),
    'type' => $type,
    'reply' => $reply
];

if (false === $type) {
    $k = 0;
} else if (is_numeric($type)) {
    $k = $type;
} else /* if (true === $type) */ {
    $k = 1;
}

?>
<section class="comments comments:<?= $k; ?>"<?= !empty($c['c']['anchor'][2]) ? ' id="' . $c['c']['anchor'][2] . '"' : ""; ?>>
  <?= self::get(__DIR__ . DS . 'comments.header.php', $c); ?>
  <?= self::get(__DIR__ . DS . 'comments.body.php', $c); ?>
  <?= self::get(__DIR__ . DS . 'comments.footer.php', $c); ?>
</section>
<?php endif; ?>
