<?php if ($page->comments->count()): ?>
<ul class="comments" data-level="0">
  <?php foreach ($page->comments($c['chunk'] ?? 9999) as $comment): ?>
  <?php static::get(__DIR__ . DS . 'comments.li.php', array_replace($lot, [
      'comment' => $comment,
      'deep' => 0
  ])); ?>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<p><?php echo $language->alertInfoVoid($language->comment(2)); ?></p>
<?php endif; ?>