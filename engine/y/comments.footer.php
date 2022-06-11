<footer class="comments-footer">
  <?= x\comment\hook('comments-footer', [[
      'pager' => self::get(__DIR__ . D . 'comments.pager.php', $lot),
      'tasks' => x\comment\hook('comments-tasks', [[], $page, null], ' '),
      'form' => $type && 2 !== $type ? ($parent ? "" : self::form('comment', $lot)) : '<p>' . i('%s are closed.', ['Comments']) . '</p>'
  ], $page, null]); ?>
</footer>