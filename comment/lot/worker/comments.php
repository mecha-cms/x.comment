<?php

$x = X . __FILE__ . X;
$test = $page->get('state.comment', $lot[0] ?? $x);

// Comment form is disabled and no comment(s)
if ($page->comments->count() === 0 && $test === 2) {
    $test = 0; // Is the same as disabled comment(s)
}

if ($test === $x || ($test !== false && $test !== 0)):

$c = [
    'c' => Extend::state('comment'),
    'type' => $test
];

if ($test === true) {
    $k = 1;
} else if ($test === false) {
    $k = 0;
} else if (is_numeric($test)) {
    $k = $test;
}

?>
<section class="comments comments:<?php echo $k ?? 1; ?>"<?php echo !empty($c['c']['anchor'][2]) ? ' id="' . $c['c']['anchor'][2] . '"' : ""; ?>>
  <?php static::get('comments.header', $c); ?>
  <?php static::get('comments.body', $c); ?>
  <?php static::get('comments.footer', $c); ?>
</section>
<?php endif; ?>