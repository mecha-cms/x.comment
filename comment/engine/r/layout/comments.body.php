<div class="comments-body">
  <?= x\comment\hook('comments-body', [[
      'content' => self::get(__DIR__ . DS . 'comments.content.php', $lot)
  ], $page, null]); ?>
</div>
