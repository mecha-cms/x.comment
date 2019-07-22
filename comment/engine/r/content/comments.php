<?php

$x = P . __FILE__ . P;
$type = $page->get('state.comment') ?? $lot[0] ?? $x;

// Comment form is disabled and no comment(s)
if ($page->comments->count() === 0 && $type === 2) {
    $type = 0; // Is the same as disabled comment(s)
}

if ($type === $x || ($type !== false && $type !== 0)):

$reply = HTTP::get('parent');
$reply = $reply ? new Comment(COMMENT . DS . $url->path(DS) . DS . $reply . '.page') : null;
$c = [
    'c' => state('comment'),
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
<section class="comments comments:<?php echo $k ?? 1; ?>"<?php echo !empty($c['c']['anchor'][2]) ? ' id="' . $c['c']['anchor'][2] . '"' : ""; ?>>
  <?php static::get(__DIR__ . DS . 'comments.header.php', $c); ?>
  <?php static::get(__DIR__ . DS . 'comments.body.php', $c); ?>
  <?php static::get(__DIR__ . DS . 'comments.footer.php', $c); ?>
</section>
<?php endif; ?>