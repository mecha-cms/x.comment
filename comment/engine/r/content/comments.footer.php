<footer class="comments-footer">
  <?php if ($type && $type !== 2): ?>
  <?= $reply ? "" : self::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php else: ?>
  <p><?= $language->alertInfoCommentX; ?></p>
  <?php endif; ?>
</footer>