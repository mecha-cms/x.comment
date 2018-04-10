<?php if ($page->comments['count']): ?>
<ul class="comments" data-level="1">
  <?php foreach ($page->comments['data'] as $comment): ?>
  <?php Shield::get('comments.li', [
      'comment' => $comment,
      'level' => 1
  ]); ?>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<p><?php echo $language->message_info_void($language->comments); ?></p>
<?php endif; ?>