<div class="comments-body">
  <?= x\comment\hook('comments-body', [[
      'content' => self::get(__DIR__ . D . 'comments.content.php', $lot)
  ], $page, null]); ?>
</div>