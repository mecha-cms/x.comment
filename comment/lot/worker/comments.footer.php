<?php if (!$page->comments->x): ?>
<footer class="comments-footer">
  <?php self::get('comments.form', ['c' => $lot['c']]); ?>
</footer>
<?php endif; ?>