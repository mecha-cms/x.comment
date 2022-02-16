<footer class="comments-footer">
  <?= x\comment\hook('comments-footer', [[
      'pager' => self::get(__DIR__ . DS . 'comments.pager.php', $lot),
      'tasks' => x\comment\hook('comments-tasks', [[], $page, null], ' '),
      'form' => $type && 2 !== $type ? ($parent ? "" : self::get(__DIR__ . DS . 'comment.form.php', $lot)) : '<p>' . i('Comments are closed.') . '</p>'
  ], $page, null]); ?>
</footer>