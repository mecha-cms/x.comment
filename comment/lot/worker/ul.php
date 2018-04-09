<?php if ($page->comments): ?>
<ul class="comments" data-level="1">
  <?php foreach ($page->comments as $k => $comment): ?>
  <?php if (!is_numeric($k)) continue; ?>
  <?php Shield::get(__DIR__ . DS . 'li.php', [
      'comment' => $comment,
      'level' => 1
  ]); ?>
  <?php endforeach; ?>
</ul>
<?php else: ?>
<p><?php echo $language->message_info_void($language->comments); ?></p>
<?php endif; ?>