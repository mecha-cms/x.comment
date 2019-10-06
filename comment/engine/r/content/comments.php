<?php

$x = P . __FILE__ . P;
$type = $page->get('state.comment') ?? $lot[0] ?? $x;

// Comment form is disabled and no comment(s)
if (!$page->comments || ($page->comments->count() === 0 && $type === 2)) {
    $type = 0; // Is the same as disabled comment(s)
}

if (
    // Make sure current page is active
    $page->x === 'page' &&
    // Make sure comment feature is active
    ($type === $x || ($type !== false && $type !== 0))
):

$reply = Get::get('parent');
$reply = $reply ? new Comment(COMMENT . $url->path(DS) . DS . $reply . '.page') : null;
$c = [
    'c' => State::get('x.comment', true),
    'type' => $type,
    'reply' => $reply
];

if ($type === true) {
    $k = 1;
} else if ($type === false) {
    $k = 0;
} else if (is_numeric($type)) {
    $k = $type;
}

?>
<section class="comments comments:<?= $k ?? 1; ?>"<?= !empty($c['c']['anchor'][2]) ? ' id="' . $c['c']['anchor'][2] . '"' : ""; ?>>
  <?= self::get(__DIR__ . DS . 'comments.header.php', $c); ?>
  <?= self::get(__DIR__ . DS . 'comments.body.php', $c); ?>
  <?= self::get(__DIR__ . DS . 'comments.footer.php', $c); ?>
</section>
<?php endif; ?>