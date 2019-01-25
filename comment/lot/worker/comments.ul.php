<?php if ($page->comments->count()): ?>
<ul class="comments" data-level="1">
  <?php foreach ($page->comments as $comment): ?>
  <?php static::get('comments.li', extend($lot, [
      'comment' => $comment,
      'level' => 1
  ], false)); ?>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<p><?php echo $language->message_info_void($language->comments); ?></p>
<?php endif; ?>