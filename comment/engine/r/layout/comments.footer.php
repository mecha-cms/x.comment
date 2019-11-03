<footer class="comments-footer">
  <?php if ($type && 2 !== $type): ?>
  <?= $reply ? "" : self::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php else: ?>
  <p><?= i('Comments are closed.'); ?></p>
  <?php endif; ?>
</footer>