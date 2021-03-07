<footer class="comments-footer">
  <?= _\lot\x\comment\layout('comments:footer', [[
      'pager' => self::get(__DIR__ . DS . 'comments.pager.php', $lot),
      'form' => $type && 2 !== $type ? ($parent ? "" : self::get(__DIR__ . DS . 'comments.form.php', $lot)) : '<p>' . i('Comments are closed.') . '</p>'
  ], $page, null]); ?>
</footer>
