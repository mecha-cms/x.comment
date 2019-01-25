<?php extract($lot, EXTR_SKIP); ?>
<footer class="comments-footer">
  <?php if ($type && $type !== 2): ?>
  <?php static::get('comments.form', $lot); ?>
  <?php else: ?>
  <p><?php echo $language->message_info_comment_x; ?></p>
  <?php endif; ?>
</footer>