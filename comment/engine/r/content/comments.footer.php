<footer class="comments-footer">
  <?php if ($type && $type !== 2): ?>
  <?php $reply || static::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php else: ?>
  <p><?php echo $language->alertInfoCommentX; ?></p>
  <?php endif; ?>
</footer>