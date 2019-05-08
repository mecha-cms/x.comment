<footer class="comments-footer">
  <?php if ($type && $type !== 2): ?>
  <?php $reply || static::get('comments.form', $lot); ?>
  <?php else: ?>
  <p><?php echo $language->messageInfoCommentX; ?></p>
  <?php endif; ?>
</footer>