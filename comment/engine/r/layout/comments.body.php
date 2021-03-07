<div class="comments-body">
  <?= _\lot\x\comment\layout('comments:body', [[
      'content' => self::get(__DIR__ . DS . 'comments.content.php', $lot)
  ], $page, null]); ?>
</div>
