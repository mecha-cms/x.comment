<?php if ($page->comments->count()): ?>
<ul class="comments" data-level="0">
  <?php foreach ($page->comments($lot['c']['chunk'] ?? 9999) as $comment): ?>
  <?php static::get('comments.li', extend($lot, [
      'comment' => $comment,
      'deep' => 0
  ], false)); ?>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<p><?php echo $language->message_info_void($language->comments); ?></p>
<?php endif; ?>